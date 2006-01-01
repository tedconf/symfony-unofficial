[?php

/**
 * <?php echo $this->getGeneratedModuleName() ?> actions.
 *
 * @package    ##PROJECT_NAME##
 * @subpackage <?php echo $this->getGeneratedModuleName() ?>

 * @author     Your name here
 * @version    SVN: $Id$
 */
class <?php echo $this->getGeneratedModuleName() ?>Actions extends sfActions
{
  public function preExecute ()
  {
    $this->addStylesheet('/sf/css/sf_admin/main', 'last');
  }

  public function executeIndex ()
  {
    return $this->forward('<?php echo $this->getModuleName() ?>', 'list');
  }

  public function executeList ()
  {
    // sort
    if ($this->getRequestParameter('sort'))
    {
      $this->getUser()->setAttribute('sort', $this->getRequestParameter('sort'), 'sf_admin/<?php echo $this->getSingularName() ?>/sort');
      $this->getUser()->setAttribute('type', $this->getRequestParameter('type', 'asc'), 'sf_admin/<?php echo $this->getSingularName() ?>/sort');
    }

<?php if ($this->getParameterValue('list.filters')): ?>
    // filter
    if ($this->getRequestParameter('filter'))
    {
      $this->getUser()->getAttributeHolder()->removeNamespace('sf_admin/<?php echo $this->getSingularName() ?>/filters');
      foreach ($this->getRequest()->getParameterHolder()->getNames() as $key)
      {
        if (preg_match('/^filter_(.+)$/', $key, $match) && $this->getRequestParameter($key) != '')
        {
          $this->getUser()->setAttribute($match[1], $this->getRequestParameter($key), 'sf_admin/<?php echo $this->getSingularName() ?>/filters');
        }
      }
    }
<?php endif ?>

    // pager
    $this->pager = new sfPager('<?php echo $this->getSingularName() ?>', <?php echo $this->getParameterValue('list.max_per_page', 20) ?>);
    $this->pager->setCriteria($this->getListCriteria());
    $this->pager->setPage($this->getRequestParameter('page', 1));
    $this->pager->init();
  }

  public function executeEdit ()
  {
    // add javascript
    $this->addJavascript('/sf/js/prototype');
    $this->addJavascript('/sf/js/sf_admin/collapse');

    $this-><?php echo $this->getSingularName() ?> = $this->get<?php echo $this->getClassName() ?>OrCreate();

    if ($this->getRequest()->getMethod() == sfRequest::POST)
    {
      $this->update<?php echo $this->getClassName() ?>FromRequest();
      $this-><?php echo $this->getSingularName() ?>->save();

      return $this->redirect('<?php echo $this->getModuleName() ?>/edit?<?php echo $this->getPrimaryKeyUrlParams('this->') ?>.'&save=ok');
<?php //' ?>
    }
  }

  public function executeDelete ()
  {
    $this-><?php echo $this->getSingularName() ?> = <?php echo $this->getClassName() ?>Peer::retrieveByPk(<?php echo $this->getRetrieveByPkParamsForDelete() ?>);
    $this->forward404Unless($this-><?php echo $this->getSingularName() ?>);

    $this-><?php echo $this->getSingularName() ?>->delete();

    return $this->redirect('<?php echo $this->getModuleName() ?>/list');
  }

  public function handleErrorEdit()
  {
    $this->preExecute();
    $this-><?php echo $this->getSingularName() ?> = $this->get<?php echo $this->getClassName() ?>OrCreate();
    $this->update<?php echo $this->getClassName() ?>FromRequest();

    return sfView::SUCCESS;
  }

  protected function update<?php echo $this->getClassName() ?>FromRequest()
  {
<?php foreach ($this->getColumnCategories('edit.display') as $category): ?>
<?php foreach ($this->getColumns('edit.display', $category) as $name => $column): $type = $column->getCreoleType(); ?>
<?php $name = $column->getName() ?>
<?php if ($type == CreoleTypes::DATE): ?>
    list($d, $m, $y) = sfI18N::getDateForCulture($this->getRequestParameter('<?php echo $name ?>'), $this->getUser()->getCulture());
    $this-><?php echo $this->getSingularName() ?>->set<?php echo $column->getPhpName() ?>("$y-$m-$d");
<?php elseif ($type == CreoleTypes::BOOLEAN): ?>
    $this-><?php echo $this->getSingularName() ?>->set<?php echo $column->getPhpName() ?>($this->getRequestParameter('<?php echo $name ?>', 0));
<?php else: ?>
    $this-><?php echo $this->getSingularName() ?>->set<?php echo $column->getPhpName() ?>($this->getRequestParameter('<?php echo $name ?>'));
<?php endif ?>
<?php endforeach ?>
<?php endforeach ?>
  }

  protected function get<?php echo $this->getClassName() ?>OrCreate (<?php echo $this->getMethodParamsForGetOrCreate() ?>)
  {
    if (<?php echo $this->getTestPksForGetOrCreate() ?>)
    {
      $<?php echo $this->getSingularName() ?> = new <?php echo $this->getClassName() ?>();
    }
    else
    {
      $<?php echo $this->getSingularName() ?> = <?php echo $this->getClassName() ?>Peer::retrieveByPk(<?php echo $this->getRetrieveByPkParamsForGetOrCreate() ?>);

      $this->forward404Unless($<?php echo $this->getSingularName() ?>);
    }

    return $<?php echo $this->getSingularName() ?>;
  }

  protected function getListCriteria ()
  {
    $c = new Criteria();

    // sort
    if ($sort_column = $this->getUser()->getAttribute('sort', null, 'sf_admin/<?php echo $this->getSingularName() ?>/sort'))
    {
      if ($this->getUser()->getAttribute('type', null, 'sf_admin/<?php echo $this->getSingularName() ?>/sort') == 'asc')
      {
        $c->addAscendingOrderByColumn($sort_column);
      }
      else
      {
        $c->addDescendingOrderByColumn($sort_column);
      }
    }

<?php if ($this->getParameterValue('list.filters')): ?>
    // filter
    $this->filters = $this->getUser()->getAttributeHolder()->getAll('sf_admin/<?php echo $this->getSingularName() ?>/filters');
<?php foreach ($this->getColumns('list.filters') as $column): $type = $column->getCreoleType() ?>
    if (isset($this->filters['<?php echo $column->getName() ?>']))
    {
      $c->add(<?php echo $this->getPeerClassName() ?>::<?php echo strtoupper($column->getName()) ?>, $this->filters['<?php echo $column->getName() ?>']);
    }
<?php endforeach ?>
<?php endif ?>

    return $c;
  }
}

?]