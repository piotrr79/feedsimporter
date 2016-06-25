<?php

namespace Websolutio\SomeBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Websolutio\SomeBundle\Entity\SomeFeeds;
use Websolutio\SomeBundle\Entity\SomeRepository;

class SomeFeedsImportController extends Controller
{
	/*
	 * Feeds importer with simplexml.
	 */
	 
    public function indexAction()
    { 
		
	$em = $this->getDoctrine()->getManager();
	
	// get rss channels stored in database
	$query = $em->createQuery("SELECT f FROM WebsolutioSomeBundle:SomeFeeds f WHERE f.active = 1 ORDER BY f.created_at DESC");
        $channels = $query->getResult(); 
        
        foreach ($channels as $channel) {
			$chanlink = $channel->link;
			$chanlang = $channel->language;
			$chancoun = $channel->country;
			$chanid = $channel->id;
			         
        $url = utf8_decode($chanlink);
		$rss = simplexml_load_file($chanlink);
	    
			// read each channel retrieved from database
			if($rss) {
			$items = $rss->channel->item;
				foreach($items as $item) {
				$title = $item->title;
				$link = $item->link;
				$guid = $item->guid;
				$description = $item->description;
				$pubDate = $item->pubDate;
				
				$em = $this->getDoctrine()->getManager();
				$stored = $em->getRepository('WebsolutioSomeBundle:SomeRepository')->findOneByGuid($guid);
					
					// if rss feed item do not exist in database save it
					if (!$stored) {
					$em = $this->getDoctrine()->getManager();
					$entity = new SomeRepository('UTF-8', 'ASCII');
					$entity->setTitle($title);			
					$entity->setLink($link);
					$entity->setGuid($guid);
					$entity->setDescription($description);
					$entity->setPublishedAt(strftime("%Y-%m-%d %H:%M:%S", strtotime($pubDate)));	
														
					$entity->setCountry($chancoun);
					$entity->setLanguage($chanlang);
					$entity->setFeedchannels($this->getDoctrine()->getManager()->getReference('WebsolutioSomeBundle:SomeFeeds', $chanid));
					$entity->setFeedId($this->id = uniqid());
					$entity->setImportedAt(new \DateTime( 'now' ));	
											
					$em->persist($entity);
					$em->flush();
					$em->clear();
					}
				}
			}
		}
        
        return $this->render('WebsolutioSomeBundle:SomeTemplate:feed.html.twig');
	}
}	

