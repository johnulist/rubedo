<?php
/**
 * Rubedo -- ECM solution
 * Copyright (c) 2014, WebTales (http://www.webtales.fr/).
 * All rights reserved.
 * licensing@webtales.fr
 *
 * Open Source License
 * ------------------------------------------------------------------------------------------
 * Rubedo is licensed under the terms of the Open Source GPL 3.0 license.
 *
 * @category   Rubedo
 * @package    Rubedo
 * @copyright  Copyright (c) 2012-2014 WebTales (http://www.webtales.fr)
 * @license    http://www.gnu.org/licenses/gpl.html Open Source GPL 3.0 license
 */

namespace RubedoAPI\Rest\V1\Mailinglists;


use RubedoAPI\Entities\API\Definition\FilterDefinitionEntity;
use RubedoAPI\Entities\API\Definition\VerbDefinitionEntity;
use RubedoAPI\Rest\V1\AbstractRessource;

/**
 * Class SubscribeRessource
 *
 * @package RubedoAPI\Rest\V1
 */
class SubscribeRessource extends AbstractRessource {
    /**
     * { @inheritdoc }
     */
    public function __construct()
    {
        parent::__construct();
        $this->define();
    }

    /**
     * Subscribe to a list of mailing lists
     *
     * @param $params
     * @return array
     */
    public function postAction($params) {
        if (is_array($params['mailingLists'])) {
            $mailingLists  = &$params['mailingLists'];
        } elseif ($params['mailingLists'] === 'all') {
            $mailingLists = array();
            foreach ($this->getMailingListCollection()->getList()['data'] as $mailingListAvailable) {
                $mailingLists[] = $mailingListAvailable['id'];
            }
        } else {
            $mailingLists = (array) $params['mailingLists'];
        }
        foreach($mailingLists as &$mailingListTargeted) {
            $result = $this->getMailingListCollection()->subscribe($mailingListTargeted, $params['email']);
            if (!$result['success']) {
                return $result;
            }
        }
        return array(
            'success' => true
        );
    }

    /**
     * Unsubscribe to a list of mailing lists
     *
     * @param $params
     * @return array
     */
    public function deleteAction($params) {
        if ($params['mailingLists'] === 'all') {
            return $this->getMailingListCollection()->unSubscribeFromAll($params['email']);
        } else {
            $mailingLists = (array) $params['mailingLists'];
            foreach($mailingLists as &$mailingListTargeted) {
                $result = $this->getMailingListCollection()->unSubscribe($mailingListTargeted, $params['email']);
                if (!$result['success']) {
                    return $result;
                }
            }
        }
        return array(
            'success' => true
        );
    }

    /**
     * Define the ressource
     */
    protected function define()
    {
        $this
            ->definition
            ->setName('SubscribeToMailingList')
            ->setDescription('Subscribe to a mailing list')
            ->editVerb('post', function (VerbDefinitionEntity &$definition) {
                $this->definePost($definition);
            })
            ->editVerb('delete', function (VerbDefinitionEntity &$definition) {
                $this->defineDelete($definition);
            });
    }

    protected function definePost(VerbDefinitionEntity &$definition)
    {
        $definition
            ->setDescription('Subscribe to a list of mailing lists')
            ->addInputFilter(
                (new FilterDefinitionEntity())
                    ->setKey('email')
                    ->setDescription('Email targeted by the query')
                    ->setFilter('validate_email')
                    ->setRequired()
            )
            ->addInputFilter(
                (new FilterDefinitionEntity())
                    ->setKey('mailingLists')
                    ->setDescription('Array or string of id to delete. "all" target all mailing lists.')
                    ->setRequired()
            );
    }
    protected function defineDelete(VerbDefinitionEntity &$definition)
    {
        $definition
            ->setDescription('Unsubscribe to a list of mailing lists')
            ->addInputFilter(
                (new FilterDefinitionEntity())
                    ->setKey('email')
                    ->setDescription('Email targeted by the query')
                    ->setFilter('validate_email')
                    ->setRequired()
            )
            ->addInputFilter(
                (new FilterDefinitionEntity())
                    ->setKey('mailingLists')
                    ->setDescription('Array or string of id to delete. "all" target all mailing lists.')
                    ->setRequired()
            );
    }
}