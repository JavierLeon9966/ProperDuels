-- #!mysql
-- #{ properduels
-- #  { init
-- #    { kits
CREATE TABLE IF NOT EXISTS Kits(
  Name VARCHAR(32) NOT NULL,
  Armor LONGBLOB NOT NULL,
  Inventory LONGBLOB NOT NULL,
  Enabled TINYINT DEFAULT 1 NOT NULL,
  PRIMARY KEY(Name)
);
-- # &
CREATE PROCEDURE MigrateKits(OUT migrated TINYINT)
BEGIN
    DECLARE got_lock    TINYINT DEFAULT 0;
    DECLARE col_count   INT     DEFAULT 0;
    DECLARE good_count  INT     DEFAULT 0;

    DECLARE CONTINUE HANDLER FOR SQLEXCEPTION
        BEGIN
            IF got_lock = 1 THEN
                DO RELEASE_LOCK('kits_old_table_migration_lock');
            END IF;
        END;

    SET migrated = 0;

    SET got_lock = GET_LOCK('kits_old_table_migration_lock', 10);

    IF got_lock = 1 THEN
        SELECT COUNT(*) INTO col_count
        FROM information_schema.columns
        WHERE table_schema = DATABASE()
          AND table_name   = 'Kits';

        SELECT COUNT(*) INTO good_count
        FROM information_schema.columns
        WHERE table_schema = DATABASE()
          AND table_name   = 'Kits'
          AND column_name IN ('Name','Kit');

        IF col_count = 2 AND good_count = 2 THEN
            START TRANSACTION;
            DROP TABLE IF EXISTS Kits;
            CREATE TABLE IF NOT EXISTS Kits(
                Name VARCHAR(32) NOT NULL,
                Armor LONGBLOB NOT NULL,
                Inventory LONGBLOB NOT NULL,
                Enabled TINYINT DEFAULT 1 NOT NULL,
                PRIMARY KEY(Name)
            );
            COMMIT;

            SET migrated = 1;
        ELSE
            SELECT COUNT(*)
            INTO col_count
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME   = 'Kits'
              AND COLUMN_NAME  = 'Enabled';

            IF col_count = 0 THEN
                ALTER TABLE Kits ADD COLUMN Enabled TINYINT NOT NULL DEFAULT 1;
            END IF;
        END IF;

        DO RELEASE_LOCK('kits_old_table_migration_lock');
    END IF;
END;
-- #    }
-- #    { arenas
CREATE TABLE IF NOT EXISTS Arenas(
  Name VARCHAR(32) NOT NULL,
  LevelName VARCHAR(64) NOT NULL,
  FirstSpawnPosX DOUBLE NOT NULL,
  FirstSpawnPosY DOUBLE NOT NULL,
  FirstSpawnPosZ DOUBLE NOT NULL,
  SecondSpawnPosX DOUBLE NOT NULL,
  SecondSpawnPosY DOUBLE NOT NULL,
  SecondSpawnPosZ DOUBLE NOT NULL,
  Kit VARCHAR(32),
  PRIMARY KEY(Name),
  FOREIGN KEY(Kit) REFERENCES Kits(Name) ON DELETE SET NULL ON UPDATE CASCADE
);
-- # &
CREATE PROCEDURE MigrateArenas(OUT migrated TINYINT)
BEGIN
    DECLARE got_lock    TINYINT DEFAULT 0;
    DECLARE col_count   INT     DEFAULT 0;
    DECLARE good_count  INT     DEFAULT 0;
    DECLARE v_fk_name       VARCHAR(64);
    DECLARE v_update_action VARCHAR(30);
    DECLARE v_delete_action VARCHAR(30);

    DECLARE CONTINUE HANDLER FOR SQLEXCEPTION
        BEGIN
            IF got_lock = 1 THEN
                DO RELEASE_LOCK('arenas_old_table_migration_lock');
            END IF;
        END;

    SET migrated = 0;

    SET got_lock = GET_LOCK('arenas_old_table_migration_lock', 10);

    IF got_lock = 1 THEN
        SELECT COUNT(*) INTO col_count
        FROM information_schema.columns
        WHERE table_schema = DATABASE()
          AND table_name   = 'Arenas';

        SELECT COUNT(*) INTO good_count
        FROM information_schema.columns
        WHERE table_schema = DATABASE()
          AND table_name   = 'Arenas'
          AND column_name IN ('Name','Arena');

        IF col_count = 2 AND good_count = 2 THEN
            START TRANSACTION;
            DROP TABLE IF EXISTS Arenas;
            CREATE TABLE Arenas (
                Name             VARCHAR(32)  NOT NULL,
                LevelName        VARCHAR(64)  NOT NULL,
                FirstSpawnPosX   DOUBLE       NOT NULL,
                FirstSpawnPosY   DOUBLE       NOT NULL,
                FirstSpawnPosZ   DOUBLE       NOT NULL,
                SecondSpawnPosX  DOUBLE       NOT NULL,
                SecondSpawnPosY  DOUBLE       NOT NULL,
                SecondSpawnPosZ  DOUBLE       NOT NULL,
                Kit              VARCHAR(32),
                PRIMARY KEY (Name),
                FOREIGN KEY (Kit) REFERENCES Kits(Name) ON DELETE SET NULL ON UPDATE CASCADE
            );
            COMMIT;

            SET migrated = 1;
        ELSE
            SELECT rc.CONSTRAINT_NAME,
                   rc.UPDATE_RULE,
                   rc.DELETE_RULE
            INTO v_fk_name, v_update_action, v_delete_action
            FROM INFORMATION_SCHEMA.REFERENTIAL_CONSTRAINTS AS rc
                     JOIN INFORMATION_SCHEMA.KEY_COLUMN_USAGE AS kcu
                          ON rc.CONSTRAINT_SCHEMA = kcu.CONSTRAINT_SCHEMA
                              AND rc.CONSTRAINT_NAME  = kcu.CONSTRAINT_NAME
            WHERE rc.CONSTRAINT_SCHEMA = DATABASE()
              AND kcu.TABLE_NAME       = 'Arenas'
              AND kcu.COLUMN_NAME      = 'Kit'
            LIMIT 1;

            IF v_update_action <> 'CASCADE' THEN
                START TRANSACTION;
                SET @sql_drop = CONCAT(
                        'ALTER TABLE `Arenas` ',
                        'DROP FOREIGN KEY `', v_fk_name, '`;'
                                );
                PREPARE stmt1 FROM @sql_drop;
                EXECUTE stmt1;
                DEALLOCATE PREPARE stmt1;

                SET @sql_add = CONCAT(
                        'ALTER TABLE `Arenas` ',
                        'ADD CONSTRAINT `', v_fk_name, '` ',
                        'FOREIGN KEY (`Kit`) ',
                        'REFERENCES `Kits`(`Name`) ',
                        'ON DELETE ', v_delete_action, ' ',
                        'ON UPDATE CASCADE;'
                               );
                PREPARE stmt2 FROM @sql_add;
                EXECUTE stmt2;
                DEALLOCATE PREPARE stmt2;
                COMMIT;
            END IF;
        END IF;

        DO RELEASE_LOCK('arenas_old_table_migration_lock');
    END IF;
END;
-- #    }
-- #  }
-- #  { load_old
-- #    { kits
SELECT Name, Kit FROM Kits;
-- #    }
-- #    { arenas
SELECT Name, Arena FROM Arenas;
-- #    }
-- #  }
-- #  { register
-- #    { kit
-- #      :name string
-- #      :armor string
-- #      :inventory string
INSERT INTO Kits(Name, Armor, Inventory)
VALUES (:name, :armor, :inventory);
-- #    }
-- #    { arena
-- #      :name string
-- #      :levelName string
-- #      :firstSpawnPosX float
-- #      :firstSpawnPosY float
-- #      :firstSpawnPosZ float
-- #      :secondSpawnPosX float
-- #      :secondSpawnPosY float
-- #      :secondSpawnPosZ float
-- #      :kit ?string
INSERT INTO Arenas(
  Name,
  LevelName,
  FirstSpawnPosX,
  FirstSpawnPosY,
  FirstSpawnPosZ,
  SecondSpawnPosX,
  SecondSpawnPosY,
  SecondSpawnPosZ,
  Kit
)
VALUES (
  :name,
  :levelName,
  :firstSpawnPosX,
  :firstSpawnPosY,
  :firstSpawnPosZ,
  :secondSpawnPosX,
  :secondSpawnPosY,
  :secondSpawnPosZ,
  :kit
);
-- #    }
-- #  }
-- #  { delete
-- #    { kit
-- #      :name string
DELETE FROM Kits
WHERE Name = :name;
-- #    }
-- #    { arena
-- #      :name string
DELETE FROM Arenas
WHERE Name = :name;
-- #    }
-- #  }
-- #  { get
-- #    { kit
-- #      :name string
SELECT * FROM Kits
WHERE Name = :name
  AND Enabled = 1;
-- #    }
-- #    { arena
-- #      :name string
SELECT * FROM Arenas
WHERE Name = :name;
-- #    }
-- #  }
-- #  { get_random
-- #    { arena
SELECT * FROM Arenas
ORDER BY RAND()
LIMIT 1;
-- #    }
-- #    { kit
SELECT * FROM Kits
WHERE Enabled = 1
ORDER BY RAND()
LIMIT 1;
-- #    }
-- #  }
-- #  { list
-- #    { kits
-- #      :offset int
-- #      :limit int
SELECT * FROM Kits
LIMIT :limit OFFSET :offset;
-- #    }
-- #    { arenas
-- #      :offset int
-- #      :limit int
SELECT * FROM Arenas
LIMIT :limit OFFSET :offset;
-- #    }
-- #  }
-- #  { check_for_migration
-- #    { kits
CALL MigrateKits(@migrationNeeded);
-- # &
SELECT @migrationNeeded AS migrationNeeded;
-- #    }
-- #    { arenas
CALL MigrateArenas(@migrationNeeded);
-- # &
SELECT @migrationNeeded AS migrationNeeded;
-- #    }
-- #  }
-- #  { update
-- #    { kit
-- #      :name string
-- #      :armor string
-- #      :inventory string
-- #      :newName string
UPDATE Kits
SET Armor = :armor,
    Inventory = :inventory,
    Name = :newName
WHERE Name = :name;
-- #    }
-- #  }
-- #  { set_enabled
-- #    { kit
-- #      :name string
-- #      :enabled bool
UPDATE Kits
SET Enabled = :enabled
WHERE Name = :name;
-- #    }
-- #  }
-- #}