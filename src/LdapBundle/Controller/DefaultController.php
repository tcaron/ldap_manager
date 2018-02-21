<?php

namespace LdapBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Ldap\Ldap;
use Symfony\Component\Ldap\Entry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
class DefaultController extends Controller
{   

    public function authAction(Request $request)
    {
	return $this->render('LdapBundle:Default:login.html.twig');
    }

     public function deleteAllAction(Request $request)
     {
	$ldap = Ldap::create('ext_ldap', array('connection_string' => 'ldap://localhost'));	
	$ldap->bind("cn=admin,dc=bla,dc=com","bla");
	$em = $ldap->getEntryManager();
	$query = $ldap->query('ou=people,dc=bla,dc=com','(objectClass=*)',array("filter"=>array('*')));
	$results = $query->execute()->toArray();
if ($request->getSession()->get('username') == "admin"){
	foreach ($results as $r)
	{	
	if (!is_null($r->getAttribute('uid'))){
	$entry = new Entry('uid='.$r->getAttribute('uid')[0].',ou=people,dc=bla,dc=com');
	$em->remove($entry);}
		
	}
}
	return $this->redirectToRoute('ldap_homepage');
     }	

     public function loginAjaxAction(Request $request)
    {   
	$session = $request->getSession();
	$ldap = Ldap::create('ext_ldap', array('connection_string' => 'ldap://localhost'));
	
	if (!is_null($request->get('username'))){
	if ($request->get('username') == "admin"){
	$ldap->bind("cn=admin,dc=bla,dc=com",$request->get("password"));
	$session->set('username', 'admin');
	$session->set('password', $request->get("password"));
	}
	else{
	$ldap->bind("uid=".$request->get('username').",ou=people,dc=bla,dc=com", $request->get("password"));
	$session->set('username', $request->get('username'));
	$session->set('password', $request->get("password"));
	}
	return $this->redirectToRoute('ldap_homepage');
	}
	
    }

    public function logoutAction(Request $request){
	$session = $request->getSession();
	$session->set("ursername","");
	$session->set("password","");
	return $this->render('LdapBundle:Default:login.html.twig');
	}
	
    public function indexAction(Request $request)
    {	$session = $request->getSession();
	$ldap = Ldap::create('ext_ldap', array('connection_string' => 'ldap://localhost'));
	$ldap->bind("cn=admin,dc=bla,dc=com","bla");
	$query = $ldap->query('ou=people,dc=bla,dc=com','(objectClass=*)',array("filter"=>array('*')));
	$results = $query->execute();
	return $this->render('LdapBundle:Default:index.html.twig',array("entries"=>$results));
    }

    public function jsonExtractAction(Request $request)
    {
	$ldap = Ldap::create('ext_ldap', array('connection_string' => 'ldap://localhost'));
	$ldap->bind("cn=admin,dc=bla,dc=com","bla");
	$query = $ldap->query('ou=people,dc=bla,dc=com','(objectClass=*)',array("filter"=>array('*')));
	$results = $query->execute()->toArray();	
	$data= array();
if ($request->getSession()->get('username') == "admin"){
	foreach ($results as $r){
	if ( $r->getAttribute('uid') != null){
	 $data[] = array(
	'objectClass' => array($r->getAttributes('objectClass')),
	'sn' => array($r->getAttribute('sn')),
	'uid' => array($r->getAttribute('uid')),
	'uidNumber' => array($r->getAttribute('uidNumber')),
	'gidNumber' => array($r->getAttribute('gidNumber')),
	'givenName'=> array($r->getAttribute('givenName')),
	'cn' => array($r->getAttribute('cn')),	
	'homeDirectory'=>array($r->getAttribute('homeDirectory')),
	'userPassword'=> array($r->getAttribute('userPassword')),
	'description'=>array($r->getAttribute('description'))
	);
	}
	}
}
	return new JsonResponse($data);

	
    }
    public function jsonImportAction(Request $request)
    {
	$ldap = Ldap::create('ext_ldap', array('connection_string' => 'ldap://localhost'));
	$ldap->bind("cn=admin,dc=bla,dc=com","bla");
	$em = $ldap->getEntryManager();	
	if ($request->getSession()->get('username') == "admin"){
if ($request->get('submit')){
	
	foreach ( json_decode($request->get('json')) as $test){
	
 	$entry = new Entry('uid='.$test->{'uid'}[0][0].',ou=people,dc=bla,dc=com', array(
    	'objectClass' => array('top','person','organizationalPerson','inetOrgPerson','posixAccount','shadowAccount'),
	'sn' => array($test->{'sn'}[0][0]),
	'uid' => array($test->{'uid'}[0][0]),
	'uidNumber' => array($test->{'uidNumber'}[0][0]),
	'gidNumber' => array($test->{'gidNumber'}[0][0]),
	'givenName'=> array($test->{'givenName'}[0][0]),
	'cn' => array($test->{'cn'}[0][0]),	
	'homeDirectory'=>array($test->{'homeDirectory'}[0][0]),
	'userPassword'=> array($test->{'userPassword'}[0][0]),
	'description'=>array($test->{'description'}[0][0])
	));
	$em->add($entry);	
	}
	return $this->redirectToRoute('ldap_homepage');
	}
}
	return $this->render('LdapBundle:Default:import.html.twig'); 
	
    }
    public function ajoutAction(Request $request)
    { 	$ldap = Ldap::create('ext_ldap', array('connection_string' => 'ldap://localhost'));	
	$ldap->bind("cn=admin,dc=bla,dc=com","bla");
	$em = $ldap->getEntryManager();
	if ($request->getSession()->get('username') == "admin"){
       if ( $request->get('submit-form')){
	
	 $querys = $ldap->query('dc=bla,dc=com','(objectClass=*)',array("filter"=>array('*')));
	 $results = $querys->execute();
	 $test = $results->toArray();
	 $test2 = end($test);
	 $entry = new Entry('uid='.$request->get('uid').',ou=people,dc=bla,dc=com', array(
    	'objectClass' => array('top','person','organizationalPerson','inetOrgPerson','posixAccount','shadowAccount'),
	'sn' => array($request->get('sn')),
	'uid' => array($request->get('uid')),
	'uidNumber' => array((string)$test2->getAttribute('uidNumber')[0]+1),
	'gidNumber' => array((string)$test2->getAttribute('uidNumber')[0]+1),
	'givenName'=> array($request->get('givenName')),
	'cn' => array($request->get('cn')),	
	'homeDirectory'=>array('/home/'.$request->get('givenName').''),
	'userPassword'=> array($request->get('password')),
	'description'=>array($request->get('description'))
	));
	$em->add($entry);
	return $this->redirectToRoute('ldap_homepage');
	}
	}
	
      return $this->render('LdapBundle:Default:ajout.html.twig');
    }

    public function modifyAction(Request $request){
   
	$ldap = Ldap::create('ext_ldap', array('connection_string' => 'ldap://localhost'));	
	$ldap->bind("cn=admin,dc=bla,dc=com","bla");
	$em = $ldap->getEntryManager();
	$query = $ldap->query('ou=people,dc=bla,dc=com','(objectClass=*)',array("filter"=>array('*')));
	$results = $query->execute()->toArray();
if ($request->getSession()->get('username') == "admin"){       
	if ( $request->get('submit-form')){
	 $query = $ldap->query('uid='.$request->get('uid').',ou=people,dc=bla,dc=com','(objectClass=*)',array("filter"=>array('*')));
       	 $result = $query->execute();
	 $entry = $result[0] ;
	 if($request->get('sn') != "")$entry->setAttribute('sn',array($request->get('sn')));
	 if($request->get('cn') != "")$entry->setAttribute('cn',array($request->get('cn')));
	 if($request->get('userPassword') != "")$entry->setAttribute('userPassword',array($request->get('password')));
	 if($request->get('givenName') != "")$entry->setAttribute('givenName',array($request->get('givenName')));
	 if($request->get('description') != "")$entry->setAttribute('description',array($request->get('description')));
	 if($request->get('givenName') != "")$entry->setAttribute('homeDirectory',array('/home/'.$request->get('givenName').''));
	 $em->update($entry);
	return $this->redirectToRoute('ldap_homepage');
	}
}
	return $this->render('LdapBundle:Default:modify.html.twig',array("users"=>$results));		
    }
  
    public function deleteAction(Request $request){
	$ldap = Ldap::create('ext_ldap', array('connection_string' => 'ldap://localhost'));	
	$ldap->bind("cn=admin,dc=bla,dc=com","bla");
	$em = $ldap->getEntryManager();
	if ($request->getSession()->get('username') == "admin"){
	$entry = new Entry('uid='.$request->get('hidden-val').',ou=people,dc=bla,dc=com');
	$em->remove($entry);
	}
	
	return $this->redirectToRoute('ldap_homepage');
   }

  public function indexGroupAction(){
	
	$ldap = Ldap::create('ext_ldap', array('connection_string' => 'ldap://localhost'));	
	$ldap->bind("cn=admin,dc=bla,dc=com","bla");
	$query = $ldap->query('dc=bla,dc=com','(objectClass=posixGroup)',array("filter"=>array('*')));
	$results = $query->execute();
	return $this->render('LdapBundle:Default:group.html.twig',array("entries"=>$results));

   }

   public function addGroupAction(Request $request){
	
	$ldap = Ldap::create('ext_ldap', array('connection_string' => 'ldap://localhost'));	
	$ldap->bind("cn=admin,dc=bla,dc=com","bla");
	$em = $ldap->getEntryManager();
if ($request->getSession()->get('username') == "admin"){
	if ( $request->get('submit-group')){
	$entry = new Entry('cn='.$request->get('group-name').',ou=group,dc=bla,dc=com',array(
	'objectClass'=>array('top','posixGroup'),
	'gidNumber'=> random_int(1000, 10000),
	'cn'=>array($request->get('group-name')),
	'description'=>array($request->get('description'))
	));
	$em->add($entry);
	return $this->redirectToRoute('ldap_group');
	}
}
	return $this->render('LdapBundle:Default:addgroup.html.twig');

   }

   public function deleteGroupAction(Request $request){
	$ldap = Ldap::create('ext_ldap', array('connection_string' => 'ldap://localhost'));	
	$ldap->bind("cn=admin,dc=bla,dc=com","bla");
	$em = $ldap->getEntryManager();
if ($request->getSession()->get('username') == "admin"){
	$entry = new Entry('cn='.$request->get('hidden-val').',ou=group,dc=bla,dc=com');
	$em->remove($entry);
	return $this->redirectToRoute('ldap_group');
}

  } 

    
  public function addMembersAction (Request $request, $cn){
	$ldap = Ldap::create('ext_ldap', array('connection_string' => 'ldap://localhost'));	
	$ldap->bind("cn=admin,dc=bla,dc=com","bla");
	$query = $ldap->query('ou=people,dc=bla,dc=com','(objectClass=*)',array("filter"=>array('*')));
	$results = $query->execute()->toArray();
	$em = $ldap->getEntryManager();
if ($request->getSession()->get('username') == "admin"){
	if ($request->get("add-submit")){
	$query2 = $ldap->query('cn='.$cn.',ou=group,dc=bla,dc=com','(objectClass=*)',array("filter"=>array('*')));
	$result2 = $query2->execute();
	$entry2 = $result2[0];
	/*if ($entry2->getAttribute('memberUid')){
	$_old = $entry2->getAttribute('memberUid');
	array_push($_old,$request->get('uid'));
	$entry2->setAttribute('memberUid',$_old);
	}*/
	//else{$entry2->setAttribute('memberUid',array($request->get('uid')));}
	$_old = $entry2->getAttribute('memberUid');	
	$entry2->setAttribute('memberUid',array($_old,$request->get('uid')));
	$em->update($entry2);
	}
}
	return $this->render('LdapBundle:Default:addgroupmembers.html.twig',array("cn"=> $cn, "users"=> $results) );
   }
}
