DECLARE
  v_count NUMBER;
BEGIN
  SELECT COUNT(*) INTO v_count FROM dba_users WHERE username = UPPER('&1');
  IF v_count > 0 THEN
    EXECUTE IMMEDIATE 'DROP USER &1 CASCADE';
  END IF;
END;
/
EXIT;