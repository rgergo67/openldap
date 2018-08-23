<?php

return [

    /*
     |--------------------------------------------------------------------------
     | LDAP sync
     |--------------------------------------------------------------------------
     |
     | Should we sync to ldap real time?
     | example : true
     |
     */
    'sync' => env('OPENLDAP_SYNC'),

    /*
     |--------------------------------------------------------------------------
     | Host
     |--------------------------------------------------------------------------
     |
     | Configure your LDAP host
     | example : 127.0.0.1 or foobar.com
     |
     */
    'host' => env('OPENLDAP_HOST'),

    /*
     |--------------------------------------------------------------------------
     | Port
     |--------------------------------------------------------------------------
     |
     | Configure your LDAP port
     | example : default 389
     |
     */
    'port' => '636',

    /*
     |--------------------------------------------------------------------------
     | Dn
     |--------------------------------------------------------------------------
     |
     | example : 'dc=domain,dc=com'
     |
     */
    'dn' => env('OPENLDAP_DN'),

    /*
     |--------------------------------------------------------------------------
     | Version
     |--------------------------------------------------------------------------
     |
     | LDAP protocol version (2 or 3)
     |
     */
    'version' => '3',

    /*
     |--------------------------------------------------------------------------
     | UserDn
     |--------------------------------------------------------------------------
     |
     | basedn for users
     | example : 'ou=people,dc=uni-pannon,dc=hu'
     |
     */
    'base_user_dn'    => env('OPENLDAP_BASE_USER_DN'),

    /*
     |--------------------------------------------------------------------------
     | EmployeeDn
     |--------------------------------------------------------------------------
     |
     | basedn for employees
     | example : 'ou=employees,ou=people,dc=uni-pannon,dc=hu'
     |
     */
    'base_employee_dn'    => env('OPENLDAP_BASE_EMPLOYEE_DN'),

    /*
     |--------------------------------------------------------------------------
     | StudentDn
     |--------------------------------------------------------------------------
     |
     | basedn for students
     | example : 'cn=students,ou=people,dc=uni-pannon,dc=hu'
     |
     */
    'base_student_dn'    => env('OPENLDAP_BASE_STUDENT_DN'),

    /*
     |--------------------------------------------------------------------------
     | GroupDn
     |--------------------------------------------------------------------------
     |
     | basedn for groups
     | example : 'ou=sys,dc=uni-pannon,dc=hu'
     |
     */
    'base_group_dn'    => env('OPENLDAP_BASE_GROUP_DN'),

    /*
     |--------------------------------------------------------------------------
     | Admin Dn
     |--------------------------------------------------------------------------
     |
     | basedn for admin
     | example : 'cn=admin,dc=domain,dc=com'
     |
     */
    'admin_dn'    => env('OPENLDAP_ADMIN_DN'),

    /*
     |--------------------------------------------------------------------------
     | Admin Password
     |--------------------------------------------------------------------------
     |
     | plain
     |
     */
    'admin_password'    => env('OPENLDAP_PASSWORD'),

    /*
     |--------------------------------------------------------------------------
     | Login attribute
     |--------------------------------------------------------------------------
     |
     | login attributes for users
     | example : 'cn'
     |
     */
    'login_attribute' => 'uid',

    /*
     |--------------------------------------------------------------------------
     | User field
     |--------------------------------------------------------------------------
     |
     | user field in html form login
     | example : 'username'
     |
     */
    'user_field' => 'username',

    /*
     |--------------------------------------------------------------------------
     | User objectClasses
     |--------------------------------------------------------------------------
     |
     | objectClass arra for creating a user
     | example : ["top","person"]
     |
     */
    'user_object_class' => [
        "top",
        "person",
        "eduPerson",
        "niifPerson",
        "posixAccount",
        "inetOrgPerson",
        "radiusprofile",
        "shadowAccount",
        "sambaSamAccount",
        "schacContactLocation",
        "organizationalPerson",
        "schacLinkageIdentifiers" ,
    ],

    'system_type_object_class' => [
        "top",
        "organizationalUnit"
    ],

    'system_object_class' => [
        "top",
        "organizationalUnit"
    ],

    'group_object_class' => [
        "top",
        "posixGroup"
    ],

];

?>