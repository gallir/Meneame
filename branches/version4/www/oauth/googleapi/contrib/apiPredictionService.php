<?php
/*
 * Copyright (c) 2010 Google Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not
 * use this file except in compliance with the License. You may obtain a copy of
 * the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations under
 * the License.
 */

require_once 'service/apiModel.php';
require_once 'service/apiService.php';
require_once 'service/apiServiceRequest.php';


  /**
   * The "training" collection of methods.
   * Typical usage is:
   *  <code>
   *   $predictionService = new apiPredictionService(...);
   *   $training = $predictionService->training;
   *  </code>
   */
  class TrainingServiceResource extends apiServiceResource {


    /**
     * Submit data and request a prediction (training.predict)
     *
     * @param string $data mybucket/mydata resource in Google Storage
     * @param $postBody the {@link Input}
     * @return Output
     */
    public function predict($data, Input $postBody) {
      $params = array('data' => $data, 'postBody' => $postBody);
      $data = $this->__call('predict', array($params));
      if ($this->useObjects()) {
        return new Output($data);
      } else {
        return $data;
      }
    }
    /**
     * Begin training your model (training.insert)
     *
     * @param $postBody the {@link Training}
     * @return Training
     */
    public function insert(Training $postBody) {
      $params = array('postBody' => $postBody);
      $data = $this->__call('insert', array($params));
      if ($this->useObjects()) {
        return new Training($data);
      } else {
        return $data;
      }
    }
    /**
     * Check training status of your model (training.get)
     *
     * @param string $data mybucket/mydata resource in Google Storage
     * @return Training
     */
    public function get($data) {
      $params = array('data' => $data);
      $data = $this->__call('get', array($params));
      if ($this->useObjects()) {
        return new Training($data);
      } else {
        return $data;
      }
    }
    /**
     * Add new data to a trained model (training.update)
     *
     * @param string $data
     * @param $postBody the {@link Update}
     * @return Training
     */
    public function update($data, Update $postBody) {
      $params = array('data' => $data, 'postBody' => $postBody);
      $data = $this->__call('update', array($params));
      if ($this->useObjects()) {
        return new Training($data);
      } else {
        return $data;
      }
    }
    /**
     * Delete a trained model (training.delete)
     *
     * @param string $data mybucket/mydata resource in Google Storage
     */
    public function delete($data) {
      $params = array('data' => $data);
      $data = $this->__call('delete', array($params));
      return $data;
    }
  }

  /**
   * The "hostedmodels" collection of methods.
   * Typical usage is:
   *  <code>
   *   $predictionService = new apiPredictionService(...);
   *   $hostedmodels = $predictionService->hostedmodels;
   *  </code>
   */
  class HostedmodelsServiceResource extends apiServiceResource {


    /**
     * Submit input and request an output against a hosted model (hostedmodels.predict)
     *
     * @param string $hostedModelName The name of a hosted model
     * @param $postBody the {@link Input}
     * @return Output
     */
    public function predict($hostedModelName, Input $postBody) {
      $params = array('hostedModelName' => $hostedModelName, 'postBody' => $postBody);
      $data = $this->__call('predict', array($params));
      if ($this->useObjects()) {
        return new Output($data);
      } else {
        return $data;
      }
    }
  }



/**
 * Service definition for Prediction (v1.3).
 *
 * <p>
 * Lets you access a cloud hosted machine learning service that makes it easy to build smart apps
 * </p>
 *
 * <p>
 * For more information about this service, see the
 * <a href="http://code.google.com/apis/predict/docs/developer-guide.html" target="_blank">API Documentation</a>
 * </p>
 *
 * @author Google, Inc.
 */
class apiPredictionService extends apiService {
  public $training;
  public $hostedmodels;
  /**
   * Constructs the internal representation of the Prediction service.
   *
   * @param apiClient apiClient
   */
  public function __construct(apiClient $apiClient) {
    $this->rpcPath = '/rpc';
    $this->restBasePath = '/prediction/v1.3/';
    $this->version = 'v1.3';
    $this->serviceName = 'prediction';
    $this->io = $apiClient->getIo();

    $apiClient->addService($this->serviceName, $this->version);
    $this->training = new TrainingServiceResource($this, $this->serviceName, 'training', json_decode('{"methods": {"predict": {"scopes": ["https://www.googleapis.com/auth/prediction"], "parameters": {"data": {"required": true, "type": "string", "location": "path"}}, "request": {"$ref": "Input"}, "id": "prediction.training.predict", "httpMethod": "POST", "path": "training/{data}/predict", "response": {"$ref": "Output"}}, "insert": {"scopes": ["https://www.googleapis.com/auth/prediction"], "request": {"$ref": "Training"}, "response": {"$ref": "Training"}, "httpMethod": "POST", "path": "training", "id": "prediction.training.insert"}, "delete": {"scopes": ["https://www.googleapis.com/auth/prediction"], "parameters": {"data": {"required": true, "type": "string", "location": "path"}}, "httpMethod": "DELETE", "path": "training/{data}", "id": "prediction.training.delete"}, "update": {"scopes": ["https://www.googleapis.com/auth/prediction"], "parameters": {"data": {"required": true, "type": "string", "location": "path"}}, "request": {"$ref": "Update"}, "id": "prediction.training.update", "httpMethod": "PUT", "path": "training/{data}", "response": {"$ref": "Training"}}, "get": {"scopes": ["https://www.googleapis.com/auth/prediction"], "parameters": {"data": {"required": true, "type": "string", "location": "path"}}, "id": "prediction.training.get", "httpMethod": "GET", "path": "training/{data}", "response": {"$ref": "Training"}}}}', true));
    $this->hostedmodels = new HostedmodelsServiceResource($this, $this->serviceName, 'hostedmodels', json_decode('{"methods": {"predict": {"scopes": ["https://www.googleapis.com/auth/prediction"], "parameters": {"hostedModelName": {"required": true, "type": "string", "location": "path"}}, "request": {"$ref": "Input"}, "id": "prediction.hostedmodels.predict", "httpMethod": "POST", "path": "hostedmodels/{hostedModelName}/predict", "response": {"$ref": "Output"}}}}', true));
  }
}

class TrainingUtility extends apiModel {


}


class Training extends apiModel {

  public $kind;
  public $trainingStatus;
  public $modelInfo;
  public $id;
  public $selfLink;
  public $utility;

  public function setKind($kind) {
    $this->kind = $kind;
  }

  public function getKind() {
    return $this->kind;
  }
  
  public function setTrainingStatus($trainingStatus) {
    $this->trainingStatus = $trainingStatus;
  }

  public function getTrainingStatus() {
    return $this->trainingStatus;
  }
  
  public function setModelInfo(TrainingModelInfo $modelInfo) {
    $this->modelInfo = $modelInfo;
  }

  public function getModelInfo() {
    return $this->modelInfo;
  }
  
  public function setId($id) {
    $this->id = $id;
  }

  public function getId() {
    return $this->id;
  }
  
  public function setSelfLink($selfLink) {
    $this->selfLink = $selfLink;
  }

  public function getSelfLink() {
    return $this->selfLink;
  }
  
  public function setUtility($utility) {
    $this->utility = $utility;
  }

  public function getUtility() {
    return $this->utility;
  }
  
}


class TrainingModelInfoConfusionMatrix extends apiModel {


}


class Update extends apiModel {

  public $classLabel;
  public $csvInstance;

  public function setClassLabel($classLabel) {
    $this->classLabel = $classLabel;
  }

  public function getClassLabel() {
    return $this->classLabel;
  }
  
  public function setCsvInstance($csvInstance) {
    $this->csvInstance = $csvInstance;
  }

  public function getCsvInstance() {
    return $this->csvInstance;
  }
  
}


class InputInput extends apiModel {

  public $csvInstance;

  public function setCsvInstance($csvInstance) {
    $this->csvInstance = $csvInstance;
  }

  public function getCsvInstance() {
    return $this->csvInstance;
  }
  
}


class TrainingModelInfo extends apiModel {

  public $confusionMatrixRowTotals;
  public $confusionMatrix;
  public $meanSquaredError;
  public $modelType;
  public $numberInstances;
  public $numberClasses;
  public $classWeightedAccuracy;
  public $classificationAccuracy;

  public function setConfusionMatrixRowTotals($confusionMatrixRowTotals) {
    $this->confusionMatrixRowTotals = $confusionMatrixRowTotals;
  }

  public function getConfusionMatrixRowTotals() {
    return $this->confusionMatrixRowTotals;
  }
  
  public function setConfusionMatrix($confusionMatrix) {
    $this->confusionMatrix = $confusionMatrix;
  }

  public function getConfusionMatrix() {
    return $this->confusionMatrix;
  }
  
  public function setMeanSquaredError($meanSquaredError) {
    $this->meanSquaredError = $meanSquaredError;
  }

  public function getMeanSquaredError() {
    return $this->meanSquaredError;
  }
  
  public function setModelType($modelType) {
    $this->modelType = $modelType;
  }

  public function getModelType() {
    return $this->modelType;
  }
  
  public function setNumberInstances($numberInstances) {
    $this->numberInstances = $numberInstances;
  }

  public function getNumberInstances() {
    return $this->numberInstances;
  }
  
  public function setNumberClasses($numberClasses) {
    $this->numberClasses = $numberClasses;
  }

  public function getNumberClasses() {
    return $this->numberClasses;
  }
  
  public function setClassWeightedAccuracy($classWeightedAccuracy) {
    $this->classWeightedAccuracy = $classWeightedAccuracy;
  }

  public function getClassWeightedAccuracy() {
    return $this->classWeightedAccuracy;
  }
  
  public function setClassificationAccuracy($classificationAccuracy) {
    $this->classificationAccuracy = $classificationAccuracy;
  }

  public function getClassificationAccuracy() {
    return $this->classificationAccuracy;
  }
  
}


class OutputOutputMulti extends apiModel {

  public $score;
  public $label;

  public function setScore($score) {
    $this->score = $score;
  }

  public function getScore() {
    return $this->score;
  }
  
  public function setLabel($label) {
    $this->label = $label;
  }

  public function getLabel() {
    return $this->label;
  }
  
}


class Output extends apiModel {

  public $kind;
  public $outputLabel;
  public $id;
  public $outputMulti;
  public $outputValue;
  public $selfLink;

  public function setKind($kind) {
    $this->kind = $kind;
  }

  public function getKind() {
    return $this->kind;
  }
  
  public function setOutputLabel($outputLabel) {
    $this->outputLabel = $outputLabel;
  }

  public function getOutputLabel() {
    return $this->outputLabel;
  }
  
  public function setId($id) {
    $this->id = $id;
  }

  public function getId() {
    return $this->id;
  }
  
  public function setOutputMulti(OutputOutputMulti $outputMulti) {
    $this->outputMulti = $outputMulti;
  }

  public function getOutputMulti() {
    return $this->outputMulti;
  }
  
  public function setOutputValue($outputValue) {
    $this->outputValue = $outputValue;
  }

  public function getOutputValue() {
    return $this->outputValue;
  }
  
  public function setSelfLink($selfLink) {
    $this->selfLink = $selfLink;
  }

  public function getSelfLink() {
    return $this->selfLink;
  }
  
}


class TrainingModelInfoConfusionMatrixRowTotals extends apiModel {


}


class Input extends apiModel {

  public $input;

  public function setInput(InputInput $input) {
    $this->input = $input;
  }

  public function getInput() {
    return $this->input;
  }
  
}

