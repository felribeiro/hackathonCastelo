<?php

namespace Drupal\hackathon\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\hackathon\Services\AssertivaServices;
use Drupal\node\Entity\Node;
use Drupal\views\Views;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Atualiza Cadastro Form.
 */
class AtualizaCadastroForm extends FormBase {

  protected $assertivaService;

  /**
   * AtualizaCadastroForm constructor.
   * @param \Drupal\hackathon\Services\AssertivaServices $assertivaService
   */
  public function __construct(AssertivaServices $assertivaService) {
    $this->assertivaService = $assertivaService;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('hackathon.services.assertiva')
    );
  }

  /**
   * Get form ID.
   *
   * {@inheritdoc}.
   */
  public function getFormId() {
    return 'hackathon_atualiza_cadastro';
  }

  /**
   * Build form.
   *
   * {@inheritdoc}.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form = array();

    // Submit.
    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#name' => 'submit',
      '#value' => $this->t('Atualiza'),
    );

    return $form;

  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $view = Views::getView('cadastro_desatualizado');
    $view->execute();
    $users = $view->result;

    foreach($users as $row) {
      if (isset($row->_entity) && is_object($row->_entity) && isset($row->_entity->nid->value)) {
        $nid = $row->_entity->nid->value;
        $cpf = $row->_entity->field_cpf->value;

        if ($nid && $cpf) {
          $data = $this->assertivaService->atualizaCadastro($cpf);
          $node = Node::load($nid);
          if (isset($data->rendaBeneficioAssistencial[0])) {
            $node->set('field_renda_beneficio_assistenci', $data->rendaBeneficioAssistencial[0]->valorBeneficio);
          }
          if (isset($data->rendaEmpregador[0])) {
            $node->set('field_faixa_de_renda', $data->rendaEmpregador[0]->faixaRenda);
          }
          $node->set('field_atualizado', TRUE);
          $node->save();
        }
      }
    }

    drupal_set_message('Todos os cadastros foram atualizados!');
  }

}
