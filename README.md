# Openldap-laravel

With this package you can add users to OpenLdap, edit and delete them, manage their attributes and add them to posix groups. The package is used to a specific project with specific needs,  feel free to modify it.


# Install
Install using composer:
```bash
composer require rgergo67/openldap
```
Run `php artisan vendor:publish` this will copy `openldap.php` config file to config directory.
Add the following line to the `config/app.php` providers array:
```
/*
 * Package Service Providers...
 */
 \Rgergo67\Openldap\OpenldapServiceProvider::class,
```

## Basic usage

To be able to use this package, your User model needs to have two things.
### DN attribute
First it has to have a `dn` attribute, which you can create virtually or get from a mysql field. In our User.php model it looks like this:
``` php
/**
     * Gets the dn attribute.
     *
     * @return string The dn attribute.
     */
    public function getDnAttribute(){
        $baseUserDn = $this->isStudent()
            ? config('openldap.base_student_dn')
            : config('openldap.base_employee_dn');
        return config('openldap.login_attribute') . "=" . $this->uid . "," . $baseUserDn;
    }
```
We store employees and students in different organization units, therefor we need to be able to decide which one the given user is. (Students have a 6 char length uid, employees have 10 char length uid). An example value for this dn attribute is `cn=rgergo6,ou=student,ou=people,dc=example,dc=com`
### ldap-format attribute
Second thing is an array called ldap-attribute. This array translates mysql fields to ldap fields. For example:
``` php
/**
 * Gets the ldap format attribute.
 *
 * @return     array  The ldap format attribute.
 */
public function getLdapFormatAttribute(){
    return [
        "objectClass" => config('openldap.user_object_class'),
        'cn' => $this->name,
        'sn' => $this->name,
        'mail' => $this->email,
        'gidNumber' => 1,
        'homeDirectory' => '/home/' . $this->username,
        'uid' => $this->uid,
        'uidNumber' => 67
    ];
}
```
If you do an `addUser($user);` the package will use this array to create a user in ldap.
### Object class
The `objectClass` attribute tells for ldap what kind of object the user is (which will specify what kind of attributes you can give it). You can edit this value in the config file, we use this:
``` php
'user_object_class' => [
    "top",
    "person",
    "eduPerson",
    "posixAccount",
    "inetOrgPerson",
    "radiusprofile",
    "shadowAccount",
    "schacContactLocation",
    "organizationalPerson",
    "schacLinkageIdentifiers" ,
],
```
In most cases the `top, person, posixAccount, InetorgPerson` is enough, but we need the others too for our project.
## Configuration
In the config file you can see `admin_dn` and `admin_password` these are used to connect to openldap, and should be defined in your .env file.
### dn
There are 4 other dn keys in config:
`base_user_dn`: if you have multiple user subtrees like we do (students, employees), then these should go below a `ou=people` node, so if you are looking for a user with `email=ratting.gergo@uni-pannon.hu` you don't have to filter twice (once in student tree, once in employee tree).
`base_student_dn` and `base_employee_dn`: we need to know where these are, so when we create a new employee, we can put it to the right place
`bsae_group_dn`: the node where your groups are.
## Groups
We use posix groups. The base_group_dn is `ou=sys,dc=example,dc=com` but you can use anything. Below this node is a `ou=joomla` and a `ou=moodle` for grouping the similar systems together. If we create a new joomla site like hr.example.com, we create a new organization unit for it `ou=hr.example.com,ou=joomla,ou=sys,dc=example,dc=com`, and a posix group below it for every joomla group (editor, registered, administrator): `cn=editor,ou=hr.example.com,ou=joomla,ou=sys,dc=example,dc=com`
## Usage
``` php
//create a new openldap object
$openldap = new Openldap();
//find a user in your mysql database
$user = User::findOrFail(1);

// Adding new user, where $user should have dn and ldap_format virtual fields
$openldap->addUser($user);

// Find this newly added user by its uid in ldap
$openldap->findUserByUid($user->uid);

// To add new password without checking the old one
$openldap->newPassword($user, "secret");

// To replace the old password, and checking if it's correct
$openldap->replacePassword($user, "oldSecret", "newSecret");

dump($group->dn);
//"cn=editor,ou=hr.example.com,ou=joomla,ou=sys,dc=example,dc=com";
dump($group->ldap_format);
/**
* [
*     "objectClass" => config('openldap.group_object_class'),
*     'cn' => "editor"
* ]
*/

//Adding a group
$openldap->addRecord($group->dn, $group->ldap_format);

// Adding user to a group
$openldap->addUserToGroup($user, $group_dn);

// Add attribute to dn. $record should be in format ['attribute' => 'value']
$record['mail' => 'rgerg67o@example.com']
$openldap->addAttribute($dn, $record);

/* List the users groups. It returns an array of group dns like this:
[
    0 => [
        'dn' => 'cn=group1,dc=example,dc=com'
    ],
    1 => [
        'dn' => 'cn=group2,dc=example,dc=com'
    ]
]
*/
$openldap->$openldap->getUserGroups($user);

// Delete user from group
$openldap->deleteUserFromGroup($user, $group_dn);

// Delete user from all groups
$openldap->deleteUserFromAllGroups($user);
```
### Updating user
If you would like to update a user, first check if its uid (or the attribute that is part of the dn) is dirty (is changed). If so, first rename it, and update afterwards. The third parameter of rename should only be used if you move the record somewhere else. Note that second parameter is only RDN, not the full DN.
```
if($user->isDirty('uid')){
    if(!$openldap->rename($user->oldDn, 'uid='.$user->uid, null))
        return false;
}

$openldap->updateUser($user);
```
One more thing: if you rename something, the first parameter should be the old DN. If you for example hook into the users updating event, the $user objects $user->uid field (that is part of the DN) would contain the updated uid (if you modified it). To get the old dn, make use of Laravels $user->getOriginal() function.
```
public function getOldDnAttribute()
{
    return "uid={$this->getOriginal('uid')},{$this->baseEmployeeDn}";
}
```

###Sync groups
Sometimes you just need to be sure the users groups are all synced. This function removes all groups from user, and adds them again.
```
$openldap->syncUserGroups($user);
```

###Sync groups on user save save
Hook into the users saved event, and run group sync there. We have a series of checkboxes with group names that we can retrieve with `request('groups')`.
```
// if all groups were deleted, return
if(!request('groups'))
    return true;

$this->openldap->deleteUserFromAllGroups($user);

foreach(request('groups') as $groupId){
    $group = Group::findOrFail($groupId);
    $this->openldap->addUserToGroup($user, $group->dn);
}
```