<?php
/**
 * PHP OpenLDAP
 *
 * @author   Ratting Gergely <ratting.gergo@uni-pannon.hu>
 * @package  PHP Openldap
 *
 * Adding new user, where $user should have dn and ldap_format virtual fields
 * addUser($user)
 *
 * Find user by uid
 * findUserByUid($user->uid)
 *
 * To add new password without checking the old
 * newPassword($user, "secret")
 *
 * To replace the old password, and checking if it's correct
 * replacePassword($user, "oldSecret", "newSecret")
 *
 * Adding user to a group
 * addUserToGroup($user, $group_dn);
 *
 * Add attribute to dn. $record should be in format ['attribute' => 'value']
 * addAttribute($dn, $record)
 *
 * List the users groups. It returns an array of group dns like this: [0 => ['dn' => 'cn=group1,dc=uni-pannon,dc=hu']. 1 => ['dn' => 'cn=group2,...']]
 * $openldap->getUserGroups($user)
 *
 * Delete user from group
 * deleteUserFromGroup($user, $group_dn);
 *
 * Delete user from all groups
 * deleteUserFromAllGroups($user);
 */
namespace Rgergo67\Openldap;

use Log;

class OpenLDAP
{
    private $connection;

    public function __construct()
    {
        $host = config('openldap.host');
        $port = config('openldap.port', 389);

        $this->connection = $this->connect($host, $port);

        $this->bind($this->connection, config('openldap.admin_dn'), config('openldap.admin_password'));
    }

    public function __destruct()
    {
        if (!is_null($this->connection)) {
            $this->close($this->connection);
        }
    }

    /**
     * Set the connection to LDAP server.
     *
     * @param string $host
     * @param string $port
     */
    public function connect($host, $port)
    {
        $connection = ldap_connect($host, $port); // must be a valid LDAP server!
        ldap_set_option($connection, LDAP_OPT_PROTOCOL_VERSION, 3);

        if($connection == false) {
            Log::emergency("Connection could not be estabilished to {$host} {$port}");
            Log::error(ldap_error($this->connection));
            ldap_get_option($this->connection, LDAP_OPT_DIAGNOSTIC_MESSAGE, $errorMessage);
            Log::error($errorMessage);
        }

        // PHP Reference says there is no control of connection status in OpenLDAP 2.x.x
        // So we'll use binding function to check connection status.
        return $connection;

    }

    public function getError()
    {
        $error = ldap_error($this->connection);
        return $error == 'Success' ? '' : $error;
    }

    /**
     * Authenticate to LDAP server.
     *
     * @param string $username eg. cn=test,ou=people,dc=example,dc=com
     * @param string $password
     */
    public function authenticate($userDn, $password)
    {
        if (empty($userDn) or empty($password)) {
            Log::emergency('Error binding to LDAP: userDn or password empty');
            return false;
        }

        //we need a new connection to bind, otherwise the already binded admin connection would be overwritten
        $authConnection = $this->connect(config('openldap.host'), config('openldap.port'));

        $isConnected = $this->bind($authConnection, $username, $password);

        //close temporary connection
        $this->close($authConnection);

        return $isConnected;
    }

    /**
     * Set the connection to LDAP server.
     *
     * @param string $connection
     * @param string $rdn
     * @param string $password
     */
    public function bind($connection, $rdn, $password)
    {
        try {
            $bind = ldap_bind($connection, $rdn, $password);
            if ($bind) {
                return true;
            }
        } catch (\Exception $e) {
            Log::emergency('Error binding to LDAP:' . $e);
            Log::error(ldap_error($this->connection));
            ldap_get_option($this->connection, LDAP_OPT_DIAGNOSTIC_MESSAGE, $errorMessage);
            Log::error($errorMessage);

            return false;
        }

    }

    /**
     * Get data with condition.
     *
     * @param string $connection
     * @param string $searchdn
     * @param string $filter
     * @param array $attributes
     * @param bool $oneLevel If true, only search in the child nodes of the searchDn, make a full deep search otherwise
     */
    public function search($searchDn, $filter, $attributes = array(), $oneLevel = false)
    {
        try {
            $search = $oneLevel
                ? ldap_list($this->connection, $searchDn, $filter, $attributes)
                : ldap_search($this->connection, $searchDn, $filter, $attributes);

            return (ldap_count_entries($this->connection, $search))
                ? ldap_get_entries($this->connection, $search)
                : false;
        } catch(\Exception $e) {
            Log::emergency("Failed search for {$filter} in {$searchDn}");
            Log::error(ldap_error($this->connection));
            ldap_get_option($this->connection, LDAP_OPT_DIAGNOSTIC_MESSAGE, $errorMessage);
            Log::error($errorMessage);

            return false;
        }
    }

    /**
     * Find user by uid
     *
     * @param string $uid The uid
     *
     * @return array attributes
     */
    public function findUserByUid($uid)
    {
        return $this->search(config('openldap.base_user_dn'), config('openldap.login_attribute') . '=' . $uid);
    }

    /**
     * Add record to LDAP.
     *
     * @param string $connection
     * @param string $adddn
     * @param array $record
     */
    public function addRecord($addDn, $record)
    {
        try {
            $t1 = microtime(true);
            $addProcess = ldap_add($this->connection, $addDn, $record);

            if ($addProcess) {
                return true;
            }else{
                throw new \Exception('Faild to add record in LDAP');
            }
        } catch(\Exception $e) {
            Log::emergency("Failed adding {$addDn}", ['data' => print_r($record, true)]);
            Log::error(ldap_error($this->connection));
            ldap_get_option($this->connection, LDAP_OPT_DIAGNOSTIC_MESSAGE, $errorMessage);
            Log::error($errorMessage);

            return false;
        }

        return false;

    }

    public function rename($oldDn, $newDn, $newParent){
        try {
            $renameProcess = ldap_rename($this->connection, $oldDn, $newDn, $newParent, TRUE);

            if ($renameProcess) {
                return true;
            }else{
                throw new \Exception('Faild to rename record in LDAP');
            }
        } catch(\Exception $e) {
            Log::emergency("Failed renaming {$oldDn} to ${newDn}");
            Log::error(ldap_error($this->connection));
            ldap_get_option($this->connection, LDAP_OPT_DIAGNOSTIC_MESSAGE, $errorMessage);
            Log::error($errorMessage);
        }

        return false;
    }

    /**
     * Adds an user.
     *
     * @param \App\User  $user   The user
     *
     * @return Bool
     */
    public function addUser($user)
    {
        //if the user already exists, update it
        if($this->findUserByUid($user->uid)) {
            return $this->updateUser($user);
        }

        return $this->addRecord($user->dn, $user->ldap_format );
    }

    /**
     * Update record. If it has multiple attributes with the same name eg. memberUid, and you give him just one for update
     * then all memberUid will be deleted and just the new one kept. If you want to add a new attribute, use addAttribute instead
     *
     * @param string $connection
     * @param string $modifydn
     * @param array $record
     */
    public function updateRecord($modifyDn, $record)
    {
        try {
            $modifyProcess = ldap_modify($this->connection, $modifyDn, $record);

            if ($modifyProcess) {
                return true;
            }else{
                throw new \Exception('Faild to rename record in LDAP');
            }
        } catch(\Exception $e) {
            Log::emergency("Failed to update {$modifyDn}", ['data' => print_r($record, true)]);
            Log::error(ldap_error($this->connection));
            ldap_get_option($this->connection, LDAP_OPT_DIAGNOSTIC_MESSAGE, $errorMessage);
            Log::error($errorMessage);

            return false;
        }

        return false;

    }

    /**
     * Updates user
     *
     * @param \App\User $user The user
     *
     * @return Bool
     */
    public function updateUser($user)
    {
        return $this->updateRecord($user->dn, $user->ldap_format);
    }

    /**
     * Adds an attribute.
     *
     * @param string $addDn The dn where you would like to add an attribute
     * @param array $record The data
     *
     * @return boolean
     */
    public function addAttribute($addDn, $record)
    {
        try {
            $addProcess = ldap_mod_add($this->connection, $addDn, $record);

            if ($addProcess) {
                return true;
            }
        } catch(\Exception $e) {
            Log::emergency("Failed modifying user {$addDn}", ['data' => print_r($record, true)]);
            Log::error(ldap_error($this->connection));
            ldap_get_option($this->connection, LDAP_OPT_DIAGNOSTIC_MESSAGE, $errorMessage);
            Log::error($errorMessage);
        }

        return false;
    }

    /**
     * Replace old password with a new one
     *
     * @param      <type>   $user         The user
     * @param      <type>   $oldPassword     The old password
     * @param      <type>   $newPassword  The new password
     *
     * @return     boolean
     */
    public function replacePassword($user, $oldPassword, $newPassword)
    {
        $authenticated = $this->authenticate($user->dn, $oldPassword);

        //old password matches => change it to new
        if($authenticated) {
            return $this->updateRecord($user->dn, ['userPassword' => $newPassword]);
        } else {
            Log::error("Wrong password when trying to change by user {$user->uid}");
        }

        return false;
    }

    /**
     * Give new password for user without old password check
     *
     * @param      <type>  $user      The user
     * @param      <type>  $password  The new password
     *
     * @return     boolean
     */
    public function newPassword($user, $password)
    {
        return $this->updateRecord($user->dn, ['userPassword' => $password]);
    }

    /**
     * Delete record LDAP.
     *
     * @param string $dn
     */
    public function deleteRecord($dn)
    {
        try {
            return (ldap_delete($this->connection, $dn));
        } catch(\Exception $e) {
            Log::emergency("Failed deleting {$dn}");
            Log::error(ldap_error($this->connection));
            ldap_get_option($this->connection, LDAP_OPT_DIAGNOSTIC_MESSAGE, $errorMessage);
            Log::error($errorMessage);
        }

        return false;
    }

    /**
     * Delete an entry with all of its child entries recursively
     *
     * @param string $dn Dn of node to delete
     * @param bool $deleteOnlyChildren Don't delete this node, only it's children
     *
     * @return bool Result of delete
     */
    public function recursiveDelete($dn, $deleteOnlyChildren = false)
    {
        $searchResult = ldap_list($this->connection, $dn, "ObjectClass=*", ['dn']);
        $children = ldap_get_entries($this->connection, $searchResult);
        $this->stripCount($children);
        foreach($children as $child){
            $result = $this->recursiveDelete($child['dn']);
            if (!$result) {
                return $result;
            }
        }

        if($deleteOnlyChildren){
            return true;
        }else{
            return $this->deleteRecord($dn);
        }
    }

    /**
     * Delete user
     *
     * @param      <type>  $user   The user
     *
     * @return     boolean
     */
    public function deleteUser($user)
    {
        return $this->deleteRecord($user->dn);
    }


    /**
     * Delete attribute from object
     *
     * @param string $deleteDn The dn you would like to delete something
     * @param array $record The record to delete eg. ['memberUid' => 'KJI1RK']
     *
     * @return boolean ( description_of_the_return_value )
     */
    public function deleteAttribute($deleteDn, $record)
    {
        try {
            return (ldap_mod_del($this->connection, $deleteDn, $record));
        } catch(\Exception $e) {
            Log::emergency("Failed deleting from {$deleteDn}", ['data' => print_r($record, true)]);
            Log::error(ldap_error($this->connection));
            ldap_get_option($this->connection, LDAP_OPT_DIAGNOSTIC_MESSAGE, $errorMessage);
            Log::error($errorMessage);
        }

        return false;
    }

    /**
     * Adds an user to group.
     *
     * @param \App\User $user The user
     * @param string $groupDn The group dn
     */
    public function addUserToGroup($memberUid, $groupDn){
        $record['memberUid'] = $memberUid;
        return $this->addAttribute($groupDn, $record);
    }

    /**
     * Gets the user groups.
     *
     * @param \App\User $user The user
     *
     * @return array The dns of the groups of the user.
     */
    public function getUserGroups($baseGroupDn, $memberUid){
        $groups = $this->search($baseGroupDn, "memberUid={$memberUid}", ["dn"]);

        if($groups) {
            $this->stripCount($groups);
        } else {
            $groups = [];
        }
        return $groups;
    }

    /**
     * Delete user from a group
     *
     * @param \App\User $user The user
     * @param string $groupDn The group dn
     *
     * @return boolean
     */
    public function deleteUserFromGroup($memberUid, $groupDn)
    {
        $record['memberUid'] = $memberUid;
        return $this->deleteAttribute($groupDn, $record);
    }

    /**
     * Delete user from all of its groups
     *
     * @param \App\User $user The user
     */
    public function deleteUserFromAllGroups($baseGroupDn, $memberUid)
    {
        $groups = $this->getUserGroups($baseGroupDn, $memberUid);
        foreach($groups as $group) {
            if(!$this->deleteUserFromGroup($memberUid, $group['dn']))
                return false;
        }
        return true;
    }

    /**
     * Delete all group of the user, and add the again (sync)
     *
     * @param <type> $user The user
     */
    public function syncUserGroups($baseGroupDn, $user){
        $this->deleteUserFromAllGroups($baseGroupDn, $user);

        foreach($user->groups as $group){
            if(!$this->addUserToGroup($user, $group->dn))
                return false;
        }
        return true;
    }

    /**
     * Close connection to LDAP.
     *
     * @param string $connection
     */
    public function close($connection)
    {
        ldap_unbind($connection);

        return true;
    }

    /**
     * Remove 'count' key from search result
     *
     * @param array $arr The array
     * @return array $arr without count keys. It doesn't have to return it, because unset works on the referenced array
     * but it is easier to work with the result this way eg. return $this->stripCount($array);
     */
    public function stripCount(array &$arr)
    {
        foreach ($arr as $key => $value) {
            if (is_array($arr[$key])) {
                $this->stripCount($arr[$key]);
            } else {
                if ($key == 'count') {
                    unset($arr[$key]);
                }
            }
        }
        return $arr;
    }


    /**
     * Create a readable array for Laravel
     * Removes count, and put dn in array key
     *
     * @param integer $entry The entry
     *
     * @return array ( description_of_the_return_value )
     */
    public function cleanUpEntry( $entry ) {
        $retEntry = array();
        for ( $i = 0; $i < $entry['count']; $i++ ) {
            if (is_array($entry[$i])) {
                $subtree = $entry[$i];
                //This condition should be superfluous so just take the recursive call
                //adapted to your situation in order to increase perf.
                if ( ! empty($subtree['dn']) and ! isset($retEntry[$subtree['dn']])) {
                    $retEntry[$subtree['dn']] = $this->cleanUpEntry($subtree);
                }
                else {
                    $retEntry[] = $this->cleanUpEntry($subtree);
                }
            }
            else {
                $attribute = $entry[$i];
                if ( $entry[$attribute]['count'] == 1 ) {
                    $retEntry[$attribute] = $entry[$attribute][0];
                } else {
                    for ( $j = 0; $j < $entry[$attribute]['count']; $j++ ) {
                        $retEntry[$attribute][] = $entry[$attribute][$j];
                    }
                }
            }
        }
        return $retEntry;
    }

}
