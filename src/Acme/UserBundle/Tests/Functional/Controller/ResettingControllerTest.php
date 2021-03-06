<?php

namespace Acme\UserBundle\Tests\Functional\Controller;

/**
 * 
*
* @author leonardo proietti <leonardo@netmeans.net>
*/
class ResettingControllerTest extends WebTestCase
{
    public function setUp()
    {
        $this->initEntityManager();
    }
    
    public function userProvider()
    {
        return array(
            array(           
              "userone@netmeans.net",
              "userone",
            ),
            array(
              "usertwo@netmeans.net",
              "usertwo",
            ),
            array(
              "userthree@netmeans.net",
              "userthree",
            )
        );
    } 
    
    /**
     * @dataProvider userProvider
     */
    public function testReset($email, $password)
    {
        $client     = static::createClient();
        $client->followRedirects(false);
                
        $crawler    = $client->request('GET', '/resetting/request');
        
        $form = $crawler->selectButton('Reset password')->form();
            
        $crawler = $client->submit(
            $form,
            array(
                'username' => $email,
            )
        );
                
        $mailCollector = $this->getMailCollector($client);
        $this->assertEquals(1, $mailCollector->getMessageCount());
        
        $client->followRedirect();

        $messages = $mailCollector->getMessages();
        preg_match_all('#\bhttps?://[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|/))#', $messages[0]->getBody(), $matches);

        $url = parse_url($matches[0][0]);
        $crawler = $client->request('GET', $url['path']);

        $this->assertTrue($client->getResponse()->isSuccessful());
        $form = $crawler->selectButton('Change password')->form();
        
        $crawler = $client->submit(
            $form,
            array(
                'fos_user_resetting_form[plainPassword][first]' => $password,
                'fos_user_resetting_form[plainPassword][second]' => $password,
            )
        );
        
        $client->followRedirect();
        $this->assertTrue($client->getResponse()->isSuccessful());
    }
    
    public function tearDown()
    {
        $userOne = $this->em->getRepository('AcmeUserBundle:UserOne')->findOneByUsername('userone');
        $userOne->setPasswordRequestedAt(null);

        $userTwo = $this->em->getRepository('AcmeUserBundle:UserTwo')->findOneByUsername('usertwo');
        $userTwo->setPasswordRequestedAt(null);
        
        $userThree = $this->em->getRepository('AcmeUserBundle:UserThree')->findOneByUsername('userthree');
        $userThree->setPasswordRequestedAt(null);
        
        $this->em->flush();
    }
    
}