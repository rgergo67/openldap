<?php namespace Rgergo67\Openldap;

use App\Http\Controllers\Controller;
use Rgergo67\Openldap\Openldap;
use App\User;

class OpenldapController extends Controller
{

    public function index()
    {
        //test code
        $user = User::findOrFail(1);
        //valid posix group dn
        $informatika_registered_dn = "cn=registered,ou=informatika.uni-pannon.hu,ou=joomla,ou=sys,dc=uni-pannon,dc=hu";

        $openldap = new Openldap();
        $openldap->addUser($user);
        $openldap->deleteUser($user);
    }

}