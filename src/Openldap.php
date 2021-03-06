<?php

namespace Rgergo67\Openldap;

use Log;

class Openldap
{
    private $connection;
    private $errors = [];

    public function __construct()
    {
        $host = config('openldap.host');
        $port = config('openldap.port', 389);

        $this->connection = $this->connect($host, $port);

        ldap_start_tls($this->connection);

        $this->bind($this->connection, config('openldap.admin_dn'), config('openldap.admin_password'));
    }

    public function __destruct()
    {
        $this->close($this->connection);
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

        if ($connection == false) {
            $this->logError("Connection could not be estabilished to {$host} {$port}");
        }

        // PHP Reference says there is no control of connection status in OpenLDAP 2.x.x
        // So we'll use binding function to check connection status.
        return $connection;

    }

    public function getErrors()
    {
        return implode('<br>', $this->errors);
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

        ldap_start_tls($authConnection);

        $isConnected = $this->bind($authConnection, $userDn, $password);

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
            return ldap_bind($connection, $rdn, $password);
        } catch (\Exception $e) {
            return $this->logError('Error binding to LDAP:' . $e);
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
    public function search($searchDn, $filter, $attributes = [], $oneLevel = false)
    {
        try {
            $search = $oneLevel
                ? ldap_list($this->connection, $searchDn, $filter, $attributes)
                : ldap_search($this->connection, $searchDn, $filter, $attributes);

            return ldap_count_entries($this->connection, $search)
                ? ldap_get_entries($this->connection, $search)
                : false;
        } catch(\Exception $e) {
            return $this->logError("Failed search for {$filter} in {$searchDn}");
        }
    }

    /**
     * Determines if the given dn exists.
     *
     * @param <type> $dn { parameter_description }
     *
     * @return <type> True if check if exists, False otherwise.
     */
    public function checkIfExists($dn)
    {
        return ldap_read($this->connection, $dn, '(objectclass=*)', ['dn']) !== false;
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
            foreach ($record as $key => $attribute) {
                if (empty($attribute)) {
                    unset($record[$key]);
                }
            }
            return ldap_add($this->connection, $addDn, $record);
        } catch(\Exception $e) {
            return $this->logError("Failed adding {$addDn}", ['data' => print_r($record, true)]);
        }
    }

    public function rename($oldDn, $newDn, $newParent){
        try {
            return ldap_rename($this->connection, $oldDn, $newDn, $newParent, TRUE);
        } catch(\Exception $e) {
            return $this->logError("Failed renaming {$oldDn} to ${newDn}");
        }
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
            foreach ($record as $key => $attribute) {
                if (empty($attribute)) {
                    $record[$key] = [];
                }
            }
            return ldap_modify($this->connection, $modifyDn, $record);
        } catch(\Exception $e) {
            return $this->logError("Failed to update {$modifyDn}", ['data' => print_r($record, true)]);
        }
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
            return ldap_mod_add($this->connection, $addDn, $record);
        } catch(\Exception $e) {
            return $this->logError("Failed modifying user {$addDn}", ['data' => print_r($record, true)]);
        }
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
            return ldap_delete($this->connection, $dn);
        } catch(\Exception $e) {
            return $this->logError("Failed deleting {$dn}");
        }
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
            if (! $result) {
                return $result;
            }
        }

        if ($deleteOnlyChildren){
            return true;
        } else {
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
            return ldap_mod_del($this->connection, $deleteDn, $record);
        } catch(\Exception $e) {
            return $this->logError("Failed deleting from {$deleteDn}", ['data' => print_r($record, true)]);
        }
    }

    /**
     * Adds an user to group.
     *
     * @param \App\User $user The user
     * @param string $groupDn The group dn
     */
    public function addUserToGroup($memberUid, $groupDn)
    {
        return $this->addAttribute($groupDn, ['memberUid' => $memberUid]);
    }

    /**
     * Gets the user groups.
     *
     * @param \App\User $user The user
     *
     * @return array The dns of the groups of the user.
     */
    public function getUserGroups($baseGroupDn, $memberUid)
    {
        $groups = $this->search($baseGroupDn, "memberUid={$memberUid}", ["dn"]);

        return $groups
            ? $this->stripCount($groups)
            : [];
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
        return $this->deleteAttribute($groupDn, ['memberUid' => $memberUid]);
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
            $this->deleteUserFromGroup($memberUid, $group['dn']);
        }
    }

    /**
     * Delete all group of the user, and add the again (sync)
     *
     * @param <type> $user The user
     */
    public function syncUserGroups($baseGroupDn, $user)
    {
        $this->deleteUserFromAllGroups($baseGroupDn, $user);

        foreach ($user->groups as $group) {
            $this->addUserToGroup($user, $group->dn);
        }
    }

    /**
     * Close connection to LDAP.
     *
     * @param string $connection
     */
    public function close($connection)
    {
        if (! is_null($connection)) {
            ldap_unbind($connection);
        }
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
        $retEntry = [];

        if ($entry === FALSE) {
            return $retEntry;
        }

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

    protected function logError($text, $data = [])
    {
        Log::emergency($text, $data);
        Log::error(ldap_error($this->connection));
        ldap_get_option($this->connection, LDAP_OPT_DIAGNOSTIC_MESSAGE, $errorMessage);
        Log::error($errorMessage);

        $this->errors[] = $text;
        $this->errors[] = $data;
        $this->errors[] = $errorMessage;

        return false;
    }

}
