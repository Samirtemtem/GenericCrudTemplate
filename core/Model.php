<?php
namespace Core;

class Model {
	protected $pdo;
	protected $table;
	protected $columns;
	protected $foreignKeys;

	public function __construct($pdo) {
		$this->pdo = $pdo;
		$this->loadTableStructure();
	}

	protected function loadTableStructure() {
		// Get table structure
		$columnsStmt = $this->pdo->query("SHOW COLUMNS FROM `{$this->table}`");
		$this->columns = $columnsStmt->fetchAll();
		
		// Load foreign keys
		$this->loadForeignKeys();
	}

	protected function loadForeignKeys() {
		// We'll implement getForeignKeyMap functionality here
		$fkQuery = "SELECT
			TABLE_NAME,
			COLUMN_NAME,
			REFERENCED_TABLE_NAME,
			REFERENCED_COLUMN_NAME
		FROM
			INFORMATION_SCHEMA.KEY_COLUMN_USAGE
		WHERE
			REFERENCED_TABLE_SCHEMA = DATABASE()
			AND TABLE_NAME = ?";
			
		$stmt = $this->pdo->prepare($fkQuery);
		$stmt->execute([$this->table]);
		$this->foreignKeys = $stmt->fetchAll(\PDO::FETCH_ASSOC);
	}

	public function create($data) {
		$fields = [];
		$values = [];
		$params = [];

		foreach ($this->columns as $column) {
			if ($column['Field'] != 'id') {
				$fields[] = "`{$column['Field']}`";
				$values[] = ":{$column['Field']}";
				$params[":{$column['Field']}"] = $data[$column['Field']] ?? null;
			}
		}

		$sql = "INSERT INTO `{$this->table}` (" . implode(", ", $fields) . ") VALUES (" . implode(", ", $values) . ")";
		$stmt = $this->pdo->prepare($sql);
		return $stmt->execute($params);
	}

	public function getColumns() {
		return $this->columns;
	}

	public function getForeignKeys() {
		return $this->foreignKeys;
	}

	public function getTableName() {
		return $this->table;
	}
}