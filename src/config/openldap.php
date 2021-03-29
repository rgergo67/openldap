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
    'sync' => env('OPENLDAP_SYNC', false),

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
    'port' => env('OPENLDAP_PORT', 389),

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
     | PhonebookDn
     |--------------------------------------------------------------------------
     |
     | basedn for phonebook entries
     | example : 'ou=phonebook,dc=uni-pannon,dc=hu'
     |
     */
    'base_phonebook_dn'    => env('OPENLDAP_BASE_PHONEBOOK_DN'),

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
     | PartnerDn
     |--------------------------------------------------------------------------
     |
     | basedn for partners
     | example : 'cn=partners,ou=people,dc=uni-pannon,dc=hu'
     |
     */
    'base_partner_dn'    => env('OPENLDAP_BASE_PARTNER_DN'),

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
     | StudentDn
     |--------------------------------------------------------------------------
     |
     | basedn for students
     | example : 'cn=students,ou=people,dc=uni-pannon,dc=hu'
     |
     */
    'base_mailclient_dn'    => env('OPENLDAP_BASE_MAILCLIENT_DN'),

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
        "niifEduPerson",
        "posixAccount",
        "inetOrgPerson",
        "radiusprofile",
        "shadowAccount",
        "sambaSamAccount",
        "extensibleObject",
        "schacContactLocation",
        "organizationalPerson",
        "schacLinkageIdentifiers" ,
    ],

    'partner_object_class' => [
        "top",
        "person",
        "eduPerson",
        "niifPerson",
        "niifEduPerson",
        "posixAccount",
        "inetOrgPerson",
        "radiusprofile",
        "shadowAccount",
        "sambaSamAccount",
        "extensibleObject",
        "schacContactLocation",
        "organizationalPerson",
        "schacLinkageIdentifiers" ,
    ],

    'position_object_class' => [
        "top",
        "person",
        "inetOrgPerson",
        "organizationalPerson",
    ],

    'student_object_class' => [
        "top",
        "person",
        "eduPerson",
        "niifPerson",
        "niifEduPerson",
        "posixAccount",
        "inetOrgPerson",
        "radiusprofile",
        "shadowAccount",
        "sambaSamAccount",
        "extensibleObject",
        "schacContactLocation",
        "organizationalPerson",
        "schacLinkageIdentifiers" ,
    ],

    'ou_object_class' => [
        "top",
        "organizationalUnit",
    ],

    'phonebook_entry_object_class' => [
        "top",
        "person",
        "organizationalPerson",
        "inetOrgPerson",
    ],

    'system_type_object_class' => [
        "top",
        "organizationalUnit",
    ],

    'system_object_class' => [
        "top",
        "organizationalUnit",
    ],

    'group_object_class' => [
        "top",
        "posixGroup",
    ],

];

?>
