<?php

namespace App\Models;

use App\Data\SQL;
use App\Structs\Setting;
use PDO;
use PDOException;

class SettingsModel extends Model
{
    public function getCurrent(): Setting {
        try {
            $this->pdo->beginTransaction();
            $currentSettingsStatement = $this->pdo->prepare(query: SQL::CURRENTSETTINGS);
            $currentSettingsStatement->execute(params: [':id' => Setting::ID]);
            $this->pdo->commit();

            return Setting::fromRow($currentSettingsStatement->fetch(PDO::FETCH_ASSOC));
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            die($e->getMessage());
        }
    }

    public function edit(Setting $settings): Setting {
        try {
            $this->pdo->beginTransaction();
            $this->pdo->prepare(query: SQL::EDITSETTINGS)
                ->execute(params: $settings->toEditParams() + [':id' => Setting::ID]);
            $this->pdo->commit();

            return $this->getCurrent();
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            die($e->getMessage());
        }
    }
}