<?php
class DevHelper_Generator_Code_Model {
	public static function generate(array $addOn, DevHelper_Config_Base $config, array $dataClass) {
		$className = self::getClassName($addOn, $config, $dataClass);
		$tableName = DevHelper_Generator_Db::getTableName($config, $dataClass['name']);
		$commentAutoGeneratedStart = DevHelper_Generator_File::COMMENT_AUTO_GENERATED_START;
		$commentAutoGeneratedEnd = DevHelper_Generator_File::COMMENT_AUTO_GENERATED_END;
		
		$intFields = DevHelper_Generator_File::varExport(DevHelper_Generator_Db::getIntFields($dataClass['fields']));
		
		$contents = <<<EOF
<?php
class $className extends XenForo_Model {

	$commentAutoGeneratedStart

	public function getList(array \$conditions = array(), array \$fetchOptions = array()) {
		\$data = \$this->getAll{$dataClass['camelCase']}(\$conditions, \$fetchOptions);
		\$list = array();
		
		foreach (\$data as \$id => \$row) {
			\$list[\$id] = \$row['{$dataClass['title_field']}'];
		}
		
		return \$list;
	}

	public function get{$dataClass['camelCase']}ById(\$id, array \$fetchOptions = array()) {
		\$data = \$this->getAll{$dataClass['camelCase']}(array ('{$dataClass['id_field']}' => \$id), \$fetchOptions);
		
		return reset(\$data);
	}
	
	public function getAll{$dataClass['camelCase']}(array \$conditions = array(), array \$fetchOptions = array()) {
		\$whereConditions = \$this->prepare{$dataClass['camelCase']}Conditions(\$conditions, \$fetchOptions);

		\$orderClause = \$this->prepare{$dataClass['camelCase']}OrderOptions(\$fetchOptions);
		\$joinOptions = \$this->prepare{$dataClass['camelCase']}FetchOptions(\$fetchOptions);
		\$limitOptions = \$this->prepareLimitFetchOptions(\$fetchOptions);

		return \$this->fetchAllKeyed(\$this->limitQueryResults("
				SELECT {$dataClass['name']}.*
					\$joinOptions[selectFields]
				FROM `$tableName` AS {$dataClass['name']}
					\$joinOptions[joinTables]
				WHERE \$whereConditions
					\$orderClause
			", \$limitOptions['limit'], \$limitOptions['offset']
		), '{$dataClass['id_field']}');
	}
		
	public function countAll{$dataClass['camelCase']}(array \$conditions = array(), array \$fetchOptions = array()) {
		\$whereConditions = \$this->prepare{$dataClass['camelCase']}Conditions(\$conditions, \$fetchOptions);

		\$orderClause = \$this->prepare{$dataClass['camelCase']}OrderOptions(\$fetchOptions);
		\$joinOptions = \$this->prepare{$dataClass['camelCase']}FetchOptions(\$fetchOptions);
		\$limitOptions = \$this->prepareLimitFetchOptions(\$fetchOptions);

		return \$this->_getDb()->fetchOne("
			SELECT COUNT(*)
			FROM `$tableName` AS {$dataClass['name']}
				\$joinOptions[joinTables]
			WHERE \$whereConditions
		");
	}
	
	public function prepare{$dataClass['camelCase']}Conditions(array \$conditions, array &\$fetchOptions) {
		\$sqlConditions = array();
		\$db = \$this->_getDb();
		
		foreach ($intFields as \$intField) {
			if (!isset(\$conditions[\$intField])) continue;
			
			if (is_array(\$conditions[\$intField])) {
				\$sqlConditions[] = "{$dataClass['name']}.\$intField IN (" . \$db->quote(\$conditions[\$intField]) . ")";
			} else {
				\$sqlConditions[] = "{$dataClass['name']}.\$intField = " . \$db->quote(\$conditions[\$intField]);
			}
		}
		
		return \$this->getConditionsForClause(\$sqlConditions);
	}
	
	public function prepare{$dataClass['camelCase']}FetchOptions(array \$fetchOptions) {
		\$selectFields = '';
		\$joinTables = '';

		return array(
			'selectFields' => \$selectFields,
			'joinTables'   => \$joinTables
		);
	}
	
	public function prepare{$dataClass['camelCase']}OrderOptions(array &\$fetchOptions, \$defaultOrderSql = '') {
		\$choices = array(
			
		);
		return \$this->getOrderByClause(\$choices, \$fetchOptions, \$defaultOrderSql);
	}

	$commentAutoGeneratedStart

}
EOF;

		return array($className, $contents);
	}
	
	public static function getClassName(array $addOn, DevHelper_Config_Base $config, array $dataClass) {
		return DevHelper_Generator_File::getClassName($addOn['addon_id'], 'Model_' . $dataClass['camelCase']);
	}
}