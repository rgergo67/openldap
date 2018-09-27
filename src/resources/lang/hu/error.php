<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Openldap Error Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used during running LDAP functions.
    | You are free to modify these language lines according to your
    | application's requirements.
    |
    */

    'add_failed' => 'Nem sikerült szinkronizálni a rekordot LDAP-ba (adatbázisba sem mentettük): :addDn',
    'add_record_failed' => 'Nem sikerült szinkronizálni a rekordot LDAP-ba (adatbázisba sem mentettük): :addDn | :error | :errorMessage',
    'attribute_deletion_failed' => 'Sikertelen LDAP törlés: :dn',
    'delete_failed' => 'Nem sikerült a törlés: :dn',
    'dn_rename_failed' => 'Nem sikerült megváltoztatni a felhasználónevet LDAP-ban. Régi: :oldDn Új: :newDn',
    'modify_failed' => 'Nem sikerült módosítani a rekordot LDAP-ba (adatbázisba sem mentettük): :modifyDn',
    'password_does_not_match' => 'Nem egyezik a megadott régi jelszó a felhasználónál: :uid',
    'search_failed' => 'Sikertelen LDAP keresés: :filter | :searchDn',
];
