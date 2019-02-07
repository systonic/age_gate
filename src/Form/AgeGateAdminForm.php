<?php

namespace Drupal\age_gate\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;

/**
 * Class AgeGateAdminForm.
 */
class AgeGateAdminForm extends ConfigFormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'age_gate_admin_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('age_gate.settings');

    $form['age_gate_age_limit'] = [
      '#type' => 'select',
      '#title' => $this->t('Age Limit'),
      '#options' => [
        21 => $this->t('21'),
        20 => $this->t('20'),
        19 => $this->t('19'),
        18 => $this->t('18'),
        17 => $this->t('17'),
        16 => $this->t('16'),
        15 => $this->t('15'),
        14 => $this->t('14'),
      ],
      '#default_value' => $config->get('age_gate_age_limit'),
      '#description' => $this->t('Set this to the age limit you require.'),
    ];

    $form['age_gate_show_dob'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enter date of birth'),
      '#default_value' => $config->get('age_gate_show_dob'),
    ];

    $form['age_gate_urls_to_skip'] = [
      '#type' => 'textarea',
      '#title' => $this->t('URLs to skip'),
      '#rows' => 3,
      '#cols' => 20,
      '#default_value' => $config->get('age_gate_urls_to_skip'),
      '#description' => $this->t('Enter the node relative urls of the pages that the age gate should ignore. In example, user or node/62 or cookie-policy. One per line.'),
    ];

    $form['age_gate_description'] = [
      '#type' => 'textarea',
      '#title' => t('Form description'),
      '#rows' => 3,
      '#cols' => 20,
      '#default_value' => $config->get('age_gate_description'),
      '#description' => $this->t('Add any description information or links you want to display under the form. Links & HTML tags: are allowed.'),
    ];

    $form['age_gate_user_agents'] = [
      '#type' => 'textarea',
      '#title' => t('Search User Agents'),
      '#rows' => 3,
      '#cols' => 20,
      '#default_value' => $config->get('age_gate_user_agents'),
      '#description' => '<p>' . $this->t('Add any extra Search bots you do not want to be blocked from indexing your site. The default is Google "Googlebot" "Googlebot-Mobile" "Googlebot-Image", "Bing "bingbot", MSN "msnbot", Yahoo "slurp".') . '</p>',
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
    $config = $this->config('age_gate.settings');

    foreach (Element::children($form) as $variable) {
      $config->set($variable, $form_state->getValue($form[$variable]['#parents']));
    }
    $config->save();

    \Drupal::service('cache_tags.invalidator')->invalidateTags(['rendered']);
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['age_gate.settings'];
  }

}
