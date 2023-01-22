<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;

trait GetNextSequenceValue {
  /**
   * Summary of getNextSequenceValue
   * @throws \Exception
   * @return int
   */
  public static function getNextSequenceValue() {
    $self = new static();
    if (!$self->getIncrementing()) {
      throw new \Exception(sprintf('Model (%s) is not auto-incremented', static::class));
    }
    $sequenceName = $self->getTable();
    return DB::selectOne(
      "SELECT AUTO_INCREMENT AS id FROM information_schema.tables WHERE table_name = '$sequenceName' and table_schema = DATABASE();"
    )->id;
  }
}
