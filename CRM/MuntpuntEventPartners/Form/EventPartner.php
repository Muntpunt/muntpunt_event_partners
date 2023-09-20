<?php

use CRM_MuntpuntEventPartners_ExtensionUtil as E;

class CRM_MuntpuntEventPartners_Form_EventPartner extends CRM_Core_Form {
  public function buildQuickForm() {
    $eventId = $this->getEventIdFromUrl();
    $event = $this->getEventDetails($eventId);

    $this->setTitle('Beheer organisator en partners van evenement: ' . $event['title']);

    //$this->addFormElements();
    //$this->addFormButtons();

    $this->assign('elementNames', $this->getRenderableElementNames());
    parent::buildQuickForm();
  }

  public function postProcess() {
    $values = $this->exportValues();

    parent::postProcess();
  }

  private function getEventIdFromUrl() {
    $vals = $this->controller->exportValues($this->_name);
    if (empty($vals)) {
      return CRM_Utils_Request::retrieveValue('event_id', 'Positive', 0, TRUE);
    }
    else {
      return $vals['event_id'];
    }
  }

  private function getEventDetails($eventId) {
    return Civi\Api4\Event::get(FALSE)
      ->addWhere('id', '=', $eventId)
      ->execute()
      ->single();
  }

  public function getRenderableElementNames() {
    $elementNames = [];
    foreach ($this->_elements as $element) {
      /** @var HTML_QuickForm_Element $element */
      $label = $element->getLabel();
      if (!empty($label)) {
        $elementNames[] = $element->getName();
      }
    }
    return $elementNames;
  }

}
