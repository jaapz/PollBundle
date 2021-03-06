<?php

namespace Desarrolla2\PollBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Desarrolla2\PollBundle\Entity\Poll;
use Desarrolla2\PollBundle\Entity\PollOption;
use Desarrolla2\PollBundle\Entity\PollOptionHit;

/**
 * PollRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class PollRepository extends EntityRepository {

    /**
     * Set Hit for a poll option
     * 
     * @param PollOption $poll_option 
     */
    public function setHit(Poll $poll, PollOption $poll_option) {
        $poll_option_ids = array();
        $em = $this->getEntityManager();
        $_poll_option_ids = $em->createQuery(
                        ' SELECT o.id AS id FROM Desarrolla2\PollBundle\Entity\PollOption o ' .
                        ' JOIN o.poll p WHERE p.id = :poll_id')
                ->setParameter('poll_id', $poll->getId())
                ->getResult();
        foreach ($_poll_option_ids as $_poll_option_id) {
            array_push($poll_option_ids, $_poll_option_id['id']);
        }
        $em->createQuery(
                        ' DELETE Desarrolla2\PollBundle\Entity\PollOptionHit h WHERE ' .
                        ' h.poll_option_id in (:poll_options_ids) AND h.session = :session')
                ->setParameter('poll_options_ids', $poll_option_ids)
                ->setParameter('session', session_id())
                ->execute();
        $hit = new PollOptionHit();
        $hit->setPollOption($poll_option);
        $em->persist($hit);
        $em->flush();
    }

    /**
     *  Retrieve array ( options => hits ) 
     * 
     * @param Poll $poll
     * @return array
     */
    public function getResult(Poll $poll) {
        $result = array();
        $em = $this->getEntityManager();
        $_results = $em->createQuery(
                        ' SELECT o, COUNT(h) AS hits FROM  Desarrolla2\PollBundle\Entity\PollOption o ' .
                        ' LEFT JOIN  o.hits h JOIN o.poll p WHERE p.id = :poll_id ' .
                        ' GROUP BY o ORDER BY hits DESC'
                )
                ->setParameter('poll_id', $poll->getId())
                ->getResult();
        foreach ($_results as $_result) {
            array_push($result, array(
                'id' => $_result[0]->getId(),
                'title' => $_result[0]->getTitle(),
                'hits' => $_result['hits'],
            ));
        }
        return $result;
    }
    /**
     * Retrieve a list of active polls
     * 
     * @param integer $limit 
     * @return array
     */
   
    public function findActives($limit = 10){
        $em = $this->getEntityManager();
        $result = $em->createQuery(
                ' SELECT p FROM  Desarrolla2\PollBundle\Entity\Poll p ' .
                ' WHERE p.is_active = 1 '
                )
                ->setMaxResults((int)$limit)
                ->getResult();
        return $result;
        
    }
}