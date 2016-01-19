<?php

namespace AppBundle\Service;


use Symfony\Component\DomCrawler\Crawler;

class BonjourMadameService {

    /**
     * @return string
     * @throws \Exception
     */
    public function getLastBonjourMadameImage()
    {
        $url = 'http://ditesbonjouralamadame.tumblr.com/';

        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_COOKIESESSION, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $return = curl_exec($curl);
        curl_close($curl);

        return $this->extractImage($return);
    }

    /**
     * @param string $html
     * @return string
     * @throws \Exception
     */
    private function extractImage($html)
    {
        $crawler = new Crawler($html);
        $crawler = $crawler->filter('.photo.post a img');
        if (null === $crawler->getNode(0)) {
            throw new \Exception('Image not found');
        }

        return $crawler->getNode(0)->attributes->item(0)->nodeValue;
    }
}
