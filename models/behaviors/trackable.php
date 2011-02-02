<?php
/**
 * Important: This behavior relies on the userModel implementing the Singleton method. For more
 *  information, see - http://www.pseudocoder.com/free-cakephp-book.
 *  
 */
class TrackableBehavior extends ModelBehavior {
	function setup(&$model, $settings = array()) {
		$this->settings[$model->alias] = array_merge(array(
			'userModel' => 'User',
			'userFields' => array()
		), $settings);
		
		foreach (array('created_by', 'modified_by', 'updated_by', 'deleted_by') as $field) {
			$alias = Inflector::classify($field);
			if (!$model->hasField($field)) {
				continue;
			}
			if (!array_key_exists($alias, $model->belongsTo)) {
				$model->bindModel(array(
					'belongsTo' => array(
						$alias => array(
							'className' => $this->settings[$model->alias]['userModel'],
							'fields' => $this->settings[$model->alias]['userFields'],
							'foreignKey' => $field
						)
					)
				), false);
			}
		}
	}
	
	function beforeSave(&$model) {
		if ($userId = $model->getUserId()) {
			$fields = array();
			if (empty($model->data[$model->alias]['id']) && $model->hasField('created_by')) {
				$fields[] = 'created_by';
			}
			if ($model->hasField('modified_by')) {
				$fields[] = 'modified_by';
			}
			if ($model->hasField('updated_by')) {
				$fields[] = 'updated_by';
			}

			foreach ($fields as $field) {
				if (!isset($model->data[$model->alias][$field])) {
					$model->data[$model->alias][$field] = $userId;
				}
			}
		}
		return true;
	}
	
	function beforeDelete(&$model) {
		if ($userId = $model->getUserId()) {
			if ($model->hasField('deleted_by')) {
				$model->data[$model->alias]['deleted_by'] = $userId;
			}
		}
		return true;
	}
	
	function getUserId(&$model) {
		$userModel = $this->settings[$model->alias]['userModel'];
		return $userModel::get('id');
	}
}