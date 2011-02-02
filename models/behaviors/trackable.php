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
		
		foreach (array('created_by', 'modified_by', 'updated_by') as $field) {
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
				));
			}
		}
	}
	
	function beforeSave(&$model) {
		$userModel = $this->settings[$model->alias]['userModel'];
		$userId = $userModel::get('id');
		
		if (!empty($userId)) {
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
}