<?php

namespace AppBundle\Command;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Common\Persistence\ObjectManager;
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
     * @return ObjectManager
     */
    protected function getManager()
    {
        return $this->getDoctrine()->getManager();
    }

    protected function configure()
    {
        $this
            ->setName('library:biography')
            ->setDescription('Parse authors biographies');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var EntityManager $em */
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

                /** @var \DOMNodeList $checks */
                $checks = $xpath->query('//div[@id="mf-section-0"]');

                if ($checks->length) {
                    $contents = $xpath->evaluate('string(p[position()=1])', $checks->item(0));

                    $contents = preg_replace('/\[\d+\]/', '', $contents);
                    if (mb_strlen($contents, 'utf-8') > 100) {
                        $oAuthor->setBiography($contents);
                        $output->writeln('set author biography');
                    } else {
                        $oAuthor->setBiography('');
                        $oAuthor->setBiographyUpdated(new \DateTime());
                    }
                }
            }

            $em->merge($oAuthor);
            $em->flush();

        }

    }
}