<?php

namespace App\Core;

/**
 * Model — Active Record style ORM base
 * Design Pattern: Active Record + Template Method
 *
 * Subclasses define: $table, $fillable, $primaryKey
 */
abstract class Model
{
    protected static string $table = '';
    protected static string $primaryKey = 'id';
    protected static array $fillable = [];

    protected array $attributes = [];
    protected bool $exists = false;

    public function __construct(array $attributes = [])
    {
        $this->fill($attributes);
    }

    // ---- Attribute access ----

    public function __get(string $name): mixed
    {
        return $this->attributes[$name] ?? null;
    }

    public function __set(string $name, mixed $value): void
    {
        $this->attributes[$name] = $value;
    }

    public function __isset(string $name): bool
    {
        return isset($this->attributes[$name]);
    }

    public function fill(array $data): self
    {
        foreach ($data as $key => $value) {
            $this->attributes[$key] = $value;
        }
        return $this;
    }

    public function toArray(): array
    {
        return $this->attributes;
    }

    // ---- Database helpers ----

    protected static function pdo(): \PDO
    {
        $config = App::getInstance()->getConfig();
        return Database::getInstance($config['db'])->getPdo();
    }

    // ---- CRUD ----

    public static function find(int $id): ?static
    {
        $table = static::$table;
        $pk = static::$primaryKey;
        $stmt = static::pdo()->prepare("SELECT * FROM {$table} WHERE {$pk} = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();

        if (!$row) {
            return null;
        }

        $model = new static($row);
        $model->exists = true;
        return $model;
    }

    public static function findBy(string $column, mixed $value): ?static
    {
        $table = static::$table;
        $stmt = static::pdo()->prepare("SELECT * FROM {$table} WHERE {$column} = :val LIMIT 1");
        $stmt->execute([':val' => $value]);
        $row = $stmt->fetch();

        if (!$row) {
            return null;
        }

        $model = new static($row);
        $model->exists = true;
        return $model;
    }

    public static function all(string $orderBy = 'id ASC'): array
    {
        $table = static::$table;
        $stmt = static::pdo()->query("SELECT * FROM {$table} ORDER BY {$orderBy}");
        $rows = $stmt->fetchAll();

        return array_map(function ($row) {
            $m = new static($row);
            $m->exists = true;
            return $m;
        }, $rows);
    }

    public static function where(string $column, mixed $value, string $orderBy = 'id DESC'): array
    {
        $table = static::$table;
        $stmt = static::pdo()->prepare(
            "SELECT * FROM {$table} WHERE {$column} = :val ORDER BY {$orderBy}"
        );
        $stmt->execute([':val' => $value]);
        $rows = $stmt->fetchAll();

        return array_map(function ($row) {
            $m = new static($row);
            $m->exists = true;
            return $m;
        }, $rows);
    }

    public static function count(string $column = null, mixed $value = null): int
    {
        $table = static::$table;
        if ($column && $value !== null) {
            $stmt = static::pdo()->prepare("SELECT COUNT(*) FROM {$table} WHERE {$column} = :val");
            $stmt->execute([':val' => $value]);
        } else {
            $stmt = static::pdo()->query("SELECT COUNT(*) FROM {$table}");
        }
        return (int)$stmt->fetchColumn();
    }

    public function save(): bool
    {
        if ($this->exists) {
            return $this->update();
        }
        return $this->insert();
    }

    protected function insert(): bool
    {
        $table = static::$table;
        $fillable = static::$fillable;
        $data = array_intersect_key($this->attributes, array_flip($fillable));

        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_map(fn($k) => ":{$k}", array_keys($data)));

        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
        $stmt = static::pdo()->prepare($sql);
        $result = $stmt->execute($data);

        if ($result) {
            $this->attributes[static::$primaryKey] = (int)static::pdo()->lastInsertId();
            $this->exists = true;
        }
        return $result;
    }

    protected function update(): bool
    {
        $table = static::$table;
        $pk = static::$primaryKey;
        $fillable = static::$fillable;
        $data = array_intersect_key($this->attributes, array_flip($fillable));

        $sets = implode(', ', array_map(fn($k) => "{$k} = :{$k}", array_keys($data)));
        $data['pk_id'] = $this->attributes[$pk];

        $sql = "UPDATE {$table} SET {$sets} WHERE {$pk} = :pk_id";
        $stmt = static::pdo()->prepare($sql);
        return $stmt->execute($data);
    }

    public function delete(): bool
    {
        $table = static::$table;
        $pk = static::$primaryKey;
        $stmt = static::pdo()->prepare("DELETE FROM {$table} WHERE {$pk} = :id");
        return $stmt->execute([':id' => $this->attributes[$pk]]);
    }

    /** Raw query helper */
    public static function query(string $sql, array $params = []): array
    {
        $stmt = static::pdo()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
