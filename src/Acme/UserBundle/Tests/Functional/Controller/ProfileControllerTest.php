<?php

//phpunit -c app/ --filter=ProfileController
//$client->getResponse()->getContent();

namespace Acme\UserBundle\Tests\Functional\Controller;

/**
 * 
*
* @author leonardo proietti <leonardo@netmeans.net>
*/
class ProfileControllerTest extends WebTestCase
{
    
    public function setUp()
    {
        $this->initEntityManager();
    }
        
    public function userProvider()
    {
        return array(
            array(           
              "userone",
              "userone",
              "userone@netmeans.net",
              "UserOne",
            ),
            array(
              "usertwo",
              "usertwo",
              "usertwo@netmeans.net",
              "UserTwo",
            )
        );
    } 
    
    /**
     * @dataProvider userProvider
     */
    public function testProfile($username, $password, $email, $entity)
    {
        $this->isSecure('/profile/edit');  
        
        $client  = static::createClient();
        $client->followRedirects(true);
        
        $client  = $this->login($client, $username, $password);
        $crawler = $client->request('GET', '/profile/edit');
                
        $form = $crawler->selectButton('Update')->form();
        
        $crawler = $client->submit(
            $form,
            array(
                'fos_user_profile_form[username]'  => $username,
                'fos_user_profile_form[email]'     => $email,
                'fos_user_profile_form[current_password]'         => $password,
            )
        );
        
        $this->assertTrue($client->getResponse()->isSuccessful());
        
        $user = $this->em->getRepository('AcmeUserBundle:'.$entity)->findOneByEmail($email);        
        $this->assertEquals($username, $user->getUsername());
        $this->assertEquals('Acme\UserBundle\Entity\\' . $entity, get_class($user));
    }
    
    /**
     * @dataProvider userProvider
     */
    public function testProfileValidation($username, $password, $email, $entity)
    {
        $client     = static::createClient();
        $client->followRedirects(true);
                
        $client  = $this->login($client, $username, $password);
        $crawler = $client->request('GET', '/profile/edit');
        
        $form = $crawler->selectButton('Update')->form();
        
        $crawler = $client->submit(
            $form,
            array(
                'fos_user_profile_form[username]'                  => null,
                'fos_user_profile_form[email]'                     => null,
                'fos_user_profile_form[current_password]'          => null,
            )
        );
        
        $this->assertRegExp("/Please enter a username/", $crawler->filter("ul li")->eq(0)->text());
        $this->assertRegExp("/This value should be the user current password/", $crawler->filter("ul li")->eq(2)->text());
    }
    
}