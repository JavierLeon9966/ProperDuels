-- #!mysql
-- #{ properduels
-- #  { init
-- #    { kits
CREATE TABLE IF NOT EXISTS Kits(
  Name VARCHAR(32) NOT NULL,
  Armor LONGBLOB NOT NULL,
  Inventory LONGBLOB NOT NULL,
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
                PRIMARY KEY(Name)
            );
            COMMIT;

            SET migrated = 1;
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
  FOREIGN KEY(Kit) REFERENCES Kits(Name) ON DELETE SET NULL
);
-- # &
CREATE PROCEDURE MigrateArenas(OUT migrated TINYINT)
BEGIN
    DECLARE got_lock    TINYINT DEFAULT 0;
    DECLARE col_count   INT     DEFAULT 0;
    DECLARE good_count  INT     DEFAULT 0;

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
                FOREIGN KEY (Kit) REFERENCES Kits(Name) ON DELETE SET NULL
            );
            COMMIT;

            SET migrated = 1;
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
WHERE Name = :name;
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
SELECT @migrationNeeded AS migrationNeeded
-- #    }
-- #  }
-- #}