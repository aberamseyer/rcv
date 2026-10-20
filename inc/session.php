<?php
require_once "functions.php";

class MySessionHandler implements SessionHandlerInterface
{
    private $s_db;
    private const DATE_FORMAT = 'Y-m-d H:i:s';

    public function open($savePath, $sessionName): bool
    {
        $this->s_db = s_db();
        return true;
    }

    // s_db() is a shared handle; closing it breaks session_regenerate_id() and later queries
    public function close(): bool
    {
        return true;
    }

    private function cutoff($maxlifetime)
    {
        return date(MySessionHandler::DATE_FORMAT, time() - $maxlifetime);
    }

    public function read($id)
    {
      $data = col("
        SELECT data
        FROM sessions
        WHERE id = '".db_esc($id, $this->s_db)."'
          AND deleted_at IS NULL
          AND last_updated >= '".$this->cutoff(ini_get('session.gc_maxlifetime'))."'", $this->s_db);
      return $data ?: '';
    }

    public function write($id, $data): bool
    {
        query("
          INSERT INTO sessions (data, id, last_updated)
          VALUES ('".db_esc($data, $this->s_db)."', '".db_esc($id, $this->s_db)."', '".date(MySessionHandler::DATE_FORMAT)."')
          ON CONFLICT (id) DO UPDATE
          SET data=excluded.data, last_updated=excluded.last_updated
        ", "", $this->s_db);
        return true;
    }

    public function destroy($id): bool
    {
        query("DELETE FROM sessions WHERE id = '".db_esc($id, $this->s_db)."'", "", $this->s_db);
        return true;
    }

    public function gc($maxlifetime)
    {
        return query("DELETE FROM sessions WHERE last_updated < '".$this->cutoff($maxlifetime)."'", "num_rows", $this->s_db);
    }
}
