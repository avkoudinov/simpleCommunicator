DECLARE
  v_count NUMBER;
BEGIN
  -- Удаляем если существует (INCLUDING CONTENTS AND DATAFILES удалит все файлы автоматически)
  SELECT COUNT(*) INTO v_count FROM dba_tablespaces WHERE tablespace_name = UPPER('&1');
  IF v_count > 0 THEN
    EXECUTE IMMEDIATE 'DROP TABLESPACE &1 INCLUDING CONTENTS AND DATAFILES CASCADE CONSTRAINTS';
  END IF;
END;
/
EXIT;