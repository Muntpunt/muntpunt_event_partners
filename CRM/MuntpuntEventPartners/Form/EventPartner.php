<?php

use CRM_MuntpuntEventPartners_ExtensionUtil as E;

class CRM_MuntpuntEventPartners_Form_EventPartner extends CRM_Core_Form {
  public function buildQuickForm() {
    $eventId = $this->getEventIdFromUrl();
    $event = $this->getEventDetails($eventId);

    $this->setTitle('Beheer organisator en partners van evenement: ' . $event['title'] . ' - ' . $event['start_date']);

    $this->addFormElements($eventId);
    $this->addFormButtons();

    $this->assign('partners', $this->getPartners($eventId));

    $this->assign('elementNames', $this->getRenderableElementNames());
    parent::buildQuickForm();
  }

  public function postProcess() {
    $values = $this->exportValues();

    $this->registerParticipant($values['event_id'], $values['partner_id'], $values['role_id']);
    CRM_Utils_System::redirect(CRM_Utils_System::url('civicrm/muntpunt-event-partners', 'reset=1&event_id=' . $values['event_id']));
    parent::postProcess();
  }

  private function addFormElements($eventId) {
    $this->add('hidden', 'event_id', $eventId);
    $this->addEntityRef('partner_id', 'Contact', ['create => TRUE', 'api' => ['params' => ['contact_type' => 'Organization']]], TRUE);
    $this->add('select', 'role_id', 'Rol', [5 => 'Organisator', 6 => 'Partner']);
  }

  private function addFormButtons() {
    $this->addButtons([
      [
        'type' => 'submit',
        'name' => 'Voeg toe',
        'isDefault' => TRUE,
      ],
      [
        'type' => 'cancel',
        'name' => E::ts('Cancel'),
      ],
    ]);
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

  private function registerParticipant($eventId, $contactId, $roleId) {
    if (!$this->isRegisteredForEvent($eventId, $contactId)) {
      $this->registerForEvent($eventId, $contactId, $roleId);
    }
  }

  private function isRegisteredForEvent($eventId, $contactId) {
    $participants = Civi\Api4\Participant::get(FALSE)
      ->addWhere('event_id', '=', $eventId)
      ->addWhere('contact_id', '=', $contactId)
      ->execute();
    if ($participants->countFetched() > 0) {
      return TRUE;
    }
    else {
      return FALSE;
    }
  }

  private function registerForEvent($eventId, $contactId, $roleId) {
    Civi\Api4\Participant::create(FALSE)
      ->addValue('event_id', $eventId)
      ->addValue('contact_id', $contactId)
      ->addValue('role_id', [$roleId])
      ->addValue('register_date', date('Y-m-d H:i:s'))
      ->execute();
  }

  private function getEventDetails($eventId) {
    return Civi\Api4\Event::get(FALSE)
      ->addWhere('id', '=', $eventId)
      ->execute()
      ->single();
  }

  private function getPartners($eventId) {
    $participants = Civi\Api4\Participant::get(FALSE)
      ->addSelect('id', 'contact_id', 'contact_id.display_name', 'role_id:label')
      ->addWhere('event_id', '=', $eventId)
      ->addClause('OR', ['role_id', 'LIKE', '%5%'], ['role_id', 'LIKE', '%6%'])
      ->addOrderBy('contact_id.display_name', 'ASC')
      ->execute();

    $partners = [];
    foreach ($participants as $participant) {

      $partners[] = [
        'name' => $participant['contact_id.display_name'],
        'role' => implode(', ', $participant['role_id:label']),
        'edit_link' => 'contact/view/participant?reset=1&action=update&id=' . $participant['id'] . '&cid=' . $participant['contact_id'] . '&context=participant',
        'delete_link' => 'contact/view/participant?reset=1&action=delete&id=' . $participant['id'] . '&cid=' . $participant['contact_id'] . '&context=participant',
      ];
    }

    return $partners;
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
