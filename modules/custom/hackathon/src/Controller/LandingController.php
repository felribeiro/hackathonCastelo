<?php

namespace Drupal\hackathon\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\node\Entity\Node;
use Symfony\Component\HttpFoundation\JsonResponse;

class LandingController extends ControllerBase {
    public function content($id) {

      $node = Node::load($id);

        return [
            '#theme' => 'landing_home'
            , '#company' => [
                'name' => 'Hackathon Cond. LTDA'
                , 'cnpj' => '21.321.321.0001/21'
            ]
            , '#bills' => $node->get('field_valor')->getValue()[0]['value']
            , '#proposal' => $node->get('field_propostas')->getValue()
            , '#id' => $id
        ];
    }
    
    public function solution() {
        $rawData = \Drupal::request()->request->all();
        
        $id = 0;
        if (isset($rawData['id'])) {
            $id = $rawData['id'];
        }
        
        if ($id && isset($rawData['solution_type'])) {
            $error = false;
            try {
                switch((int)$rawData['solution_type']) {
                    case 1:
                        $this->saveProposalApproved($id, $rawData);
                        break;

                    case 2:
                        $this->saveProposalReproved($id, $rawData);
                        break;
                }
            } catch(\Exception $ex) {
                $error = $ex->getMessage();
            }
        } else {
            $error = true;
        }
        
        $result = [
            'success' => false
        ];
        if ($error) {
            $result['message'] = $error;
        } else {
            $result['success'] = true;
        }
        
        return new JsonResponse($result);
    }
    
    private function saveProposalApproved($id, $rawData) {
        if (!empty($rawData['payment_method'])) {
            $data['payment_method'] = $rawData['payment_method'];
        } else {
            throw new \Exception('Método de pagamento não definido!');
        }

        if (!empty($rawData['payment_date'])) {
            $data['payment_date'] = $rawData['payment_date'];
        } else {
            throw new \Exception('Data de pagamento não definida!');
        }
        $proposal = Node::load($id);
        $proposal->set('field_valor', '123123');
        $proposal->set('field_metodo_que_foi_pago', '1');
        $date = new \DateTime(date("Y-m-d H:i:s", strtotime($data['payment_date'])));
        $proposal->set('field_data_pagamento', $date->format("Y-m-d"));
        try {
            $proposal->save();
        } catch(\Drupal\Core\Entity\EntityStorageException $ex) {
            throw new \Exception($ex->getMessage());
        }
    }
    
    private function saveProposalReproved($id, $rawData) {
        if (!empty($rawData['solution_justify'])) {
            $data['solution_justify'] = $rawData['solution_justify'];
        } else {
            throw new \Exception('A justificativa não foi definida!');
        }

        if (!empty($rawData['solution_justify_detail'])) {
            $data['solution_justify_detail'] = $rawData['solution_justify_detail'];
        }
        
        if (!empty($rawData['user_offer'])) {
            $data['user_offer'] = $rawData['user_offer'];
        }
        
        $proposal = Node::load($id);
        $proposal->set('field_justificativa', $data['solution_justify']);
        $proposal->set('field_detalhes_justificativa', $data['solution_justify_detail']);
        $proposal->set('field_contraproposta', $data['user_offer']);
        
        $proposal->save();
    }
    
}