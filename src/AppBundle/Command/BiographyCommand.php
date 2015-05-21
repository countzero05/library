<?php

namespace AppBundle\Command;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\DBAL\Driver\PDOConnection;
use Doctrine\DBAL\Driver\PDOStatement;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class BiographyCommand extends ContainerAwareCommand
{

    /**
     * @return Registry
     */
    protected function getDoctrine()
    {
        return $this->getContainer()->get('doctrine');
    }

    /**
     * @return EntityManager
     */
    protected function getManager()
    {
        return $this->getDoctrine()->getManager();
    }

    protected function configure()
    {
        $this
            ->setName('biography:parse')
            ->setDescription('Parse authors biographies');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getManager();

        /** @var PDOConnection $con */
        $con = $em->getConnection()->getWrappedConnection();

        /** @var PDOStatement $stmt */
        $stmt = $con->prepare('
          SELECT * FROM
	        (SELECT
	          a.id id,
	          a.name fio,
	          count(b.id) c
            FROM
              books b
              LEFT JOIN authors a ON a.id = b.author_id
            WHERE
                a.name ~* \'(\\W){1,}\'
            AND
                (extract(EPOCH FROM (current_timestamp)) - coalesce(extract(EPOCH FROM a.biography_updated::TIMESTAMP), 0)) > 86400
            GROUP BY a.id
            ) AS b
          ORDER BY
            b.c DESC LIMIT 100
        ');

        $stmt->execute();

        $authors = $stmt->fetchAll(\PDO::FETCH_OBJ);

        foreach ($authors as $author) {
            $output->writeln($author->id . ' ' . $author->fio);
            $oAuthor = $em->getRepository('AppBundle:Author')->find($author->id);
            $oAuthor->setBiographyUpdated(new \DateTime());

            $fio = $author->fio;
            $url = 'https://ru.m.wikipedia.org/w/index.php?' . http_build_query(['search' => $fio]);

            $s = file_get_contents($url);

            if ($s) {
                $doc = new \DOMDocument('1.0', 'utf-8');
                @$doc->loadHTML($s);
                $doc->normalizeDocument();

                $xpath = new \DOMXPath($doc);

                $checks = $xpath->query('//a[@id="mw-mf-last-modified"]');

                if ($checks->length) {
                    $contents = $xpath->query('//div[@id="content"]/div[position()=2]/p|//div[@id="content"]/div[position()=2]/h3/span|//div[@id="content"]/div[position()=2]/blockquote|//div[@id="content"]/div[position()=2]/ul');

                    $s = '';
                    for ($i = 0; $i < $contents->length; $i++) {
                        $text = trim($contents->item($i)->textContent);
                        switch ($contents->item($i)->nodeName) {
                            case 'p':
                                $s .= '<p>' . $text . '</p>' . "\n";
                                break;
                            case 'span':
                                $s .= '<div class="bio-heading">' . $text . '</div>' . "\n";
                                break;
                            case 'ul':
                                $s .= '<div class="bio-list">' . $text . '</div>' . "\n";
                                break;
                            case 'blockquote':
                                $s .= '<div class="bio-blockquote">' . $text . '</div>' . "\n";
                                break;
                            default:
                                $s .= '<p>' . $text . '</p>' . "\n";
                                break;
                        }
                    }

                    $s = preg_replace('/\[\d+\]/', '', $s);

                    if (mb_strlen($s, 'utf-8') > 500) {
                        $oAuthor->setBiography($s);
                        $output->writeln('set author biography');
                    }
                }
            }

            $em->merge($oAuthor);
            $em->flush();

        }

    }
}

/*else {
                continue;
                $results = $xpath->query('//ul[@class="mw-search-results"]');

                if ($results->length < 1) {
                    continue;
                }

                $el = $results[0];

                $as = $xpath->query('li/div/a', $el);

                if (!$as->length) {
                    continue;
                }

                $a = $as->item(0);

                var_dump($a->getAttribute('href'));

                die();
            }*/