<?php

namespace Drupal\hackathon\Services;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\node\Entity\Node;

/**
 * Class responsible to provide methods to handle Assertiva api.
 */
class AssertivaServices {

  public function atualizaCadastro($cpf) {

    $client = new \GuzzleHttp\Client();
    $siteUrl = 'https://services.assertivasolucoes.com.br/v1/localize/1000/consultar?cpf=' . $cpf;

    try {
      $res = $client->post($siteUrl, [
        'http_errors' => true,
        'headers' => [
          'Content-Type: multipart/form-data',
          'Authorization' => ' CFA6A7B3-99AA-4E66-B6CB-48FAB4BB3FCC '
        ]
      ]);
      return(json_decode($res->getBody()->getContents()));
    } catch (RequestException $e) {
      return($this->t('Error'));
    }

  }

  public function enviaSms() {

  }

  public function enviaEmail($inquilino_id, $proposta_id) {
    $inquilino = Node::load($inquilino_id->getValue()[0]['target_id']);
    $mailManager = \Drupal::service('plugin.manager.mail');
    $module = 'hackathon';
    $key = 'envia_proposta';
    $to = $inquilino->get('field_email')->getValue()[0]['value'];
    $params['message'] = 'Olá @@name@@, <br> Estamos entrando em contato para ' .
        'te ajudar a liquidar os débitos da taxa de condomínio.' .
        '<br> Clique no link abaixo e veja nossa proposta: <br> @@link@@';

    $params['message'] = str_replace('@@name@@', $inquilino->get('title')->getValue()[0]['value'] ,$params['message']);
    $params['message'] = str_replace('@@link@@', '<a href="' . Url::fromRoute('hackathon.landing', ['id' => $proposta_id], ['absolute' => TRUE])->toString() . '">Clique Aqui</a>' ,$params['message']);
    $params['subject'] = 'Ajude-nos a fazer um condomínio melhor.';
    $langcode = \Drupal::currentUser()->getPreferredLangcode();
    $send = true;
    $result = $mailManager->mail($module, $key, $to, $langcode, $params, NULL, $send);
  }

}
