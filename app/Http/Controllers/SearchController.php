<?php

/*
 * This file is part of Hiject.
 *
 * Copyright (C) 2016 Hiject Team
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hiject\Controller;

use Hiject\Filter\TaskProjectsFilter;

/**
 * Search Controller
 */
class SearchController extends BaseController
{
    public function index()
    {
        $projects = $this->projectUserRoleModel->getActiveProjectsByUser($this->userSession->getId());
        $query = urldecode($this->request->getStringParam('q'));
        $nb_tasks = 0;

        $paginator = $this->paginator
                ->setUrl('SearchController', 'index', array('q' => $query))
                ->setMax(30)
                ->setOrder('tasks.id')
                ->setDirection('DESC');

        if ($query !== '' && ! empty($projects)) {
            $paginator
                ->setQuery($this->taskLexer
                    ->build($query)
                    ->withFilter(new TaskProjectsFilter(array_keys($projects)))
                    ->getQuery()
                )
                ->calculate();

            $nb_tasks = $paginator->getTotal();
        }

        $this->response->html($this->helper->layout->app('search/index', array(
            'values' => array(
                'q' => $query,
                'controller' => 'SearchController',
                'action' => 'index',
            ),
            'paginator' => $paginator,
            'title' => t('Search tasks').($nb_tasks > 0 ? ' ('.$nb_tasks.')' : '')
        )));
    }

    public function activity()
    {
        $query = urldecode($this->request->getStringParam('q'));
        $events = $this->helper->projectActivity->searchEvents($query);
        $nb_events = count($events);

        $this->response->html($this->helper->layout->app('search/activity', array(
            'values' => array(
                'q' => $query,
                'controller' => 'SearchController',
                'action' => 'activity',
            ),
            'title' => t('Search in activity stream').($nb_events > 0 ? ' ('.$nb_events.')' : ''),
            'nb_events' => $nb_events,
            'events' => $events,
        )));
    }
}
