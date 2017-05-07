<?php
namespace Drupal\hackathon\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\hackathon\Services\AssertivaServices;
use Drupal\node\Entity\Node;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines a confirmation form for deleting mymodule data.
 */
class EnviaPropostaForm extends ConfirmFormBase {

  /**
   * The ID of the item to delete.
   *
   * @var string
   */
  protected $id;

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
   * {@inheritdoc}.
   */
  public function getFormId()
  {
    return 'envia_proposta_form';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    //the question to display to the user.
    return t('Você quer enviar a proposta?');
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    //this needs to be a valid route otherwise the cancel link won't appear
    return new Url('view.inquilinos_com_propostas.page_1');
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    //a brief desccription
    return t('A proposta será enviada conforme a configuração do inquilino!');
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Enviar');
  }


  /**
   * {@inheritdoc}
   */
  public function getCancelText() {
    return $this->t('Cancelar');
  }

  /**
   * {@inheritdoc}
   *
   * @param int $id
   *   (optional) The ID of the item to be deleted.
   */
  public function buildForm(array $form, FormStateInterface $form_state, $id = NULL) {
    $this->id = $id;
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $node = Node::load($this->id);
    if ($node->get('field_proposta_por_email')->getValue()
      || $node->get('field_proposta_por_sms')->getValue() ) {

      if ($node->get('field_proposta_por_email')->getValue()) {
        $this->assertivaService->enviaEmail($node->get('field_inquilino'), $this->id);
      }

      if ($node->get('field_proposta_por_sms')->getValue()) {
        $this->assertivaService->enviaSms();
      }

      $time = new \DateTime(date("Y-m-d H:i:s", time()));
      $node->set('field_data_da_ultima_proposta',$time->format("Y-m-d\TH:i:s"));
      $node->save();

      drupal_set_message('Proposta enviada com sucesso!');
    }

    $redirect = Url::fromRoute('view.inquilinos_com_propostas.page_1');
    $form_state->setRedirectUrl($redirect);
  }
}