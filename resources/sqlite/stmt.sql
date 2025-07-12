-- #!sqlite
-- #{ properduels
-- #  { init
-- #    { foreign_keys
PRAGMA foreign_keys = ON;
-- #    }
-- #    { kits
CREATE TABLE IF NOT EXISTS Kits(
  Name VARCHAR(32) NOT NULL,
  Armor LONGBLOB NOT NULL,
  Inventory LONGBLOB NOT NULL,
  Enabled BIT DEFAULT 1 NOT NULL,
  PRIMARY KEY(Name)
);
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
-- #    }
-- #  }
-- #  { load
-- #    { kits
SELECT * FROM Kits;
-- #    }
-- #    { arenas
SELECT * FROM Arenas;
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
WHERE Name =:name;
-- #    }
-- #    { arena
-- #      :name string
DELETE FROM Arenas
WHERE Name =:name;
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
ORDER BY RANDOM()
LIMIT 1;
-- #    }
-- #    { kit
SELECT * FROM Kits
WHERE Enabled = 1
ORDER BY RANDOM()
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
SELECT
    EXISTS(
        SELECT 1
        FROM pragma_table_info('Kits')
        WHERE name='Enabled'
    ) AS migrationNeeded;
-- #    }
-- #    { arenas
SELECT
    EXISTS(
        SELECT 1
        FROM pragma_foreign_key_list('Arenas')
        WHERE on_update <> 'CASCADE'
    ) AS migrationNeeded;
-- #    }
-- #  }
-- #  { migrate
-- #    { kits
ALTER TABLE Kits
    ADD COLUMN Enabled INTEGER NOT NULL DEFAULT 1;
-- #    }
-- #    { arenas
PRAGMA foreign_keys = OFF;
-- # &
SAVEPOINT migrate_arena_fk;
-- # &
CREATE TABLE IF NOT EXISTS Arenas_new (
    Name            VARCHAR(32)  NOT NULL PRIMARY KEY,
    LevelName       VARCHAR(64)  NOT NULL,
    FirstSpawnPosX  DOUBLE       NOT NULL,
    FirstSpawnPosY  DOUBLE       NOT NULL,
    FirstSpawnPosZ  DOUBLE       NOT NULL,
    SecondSpawnPosX DOUBLE       NOT NULL,
    SecondSpawnPosY DOUBLE       NOT NULL,
    SecondSpawnPosZ DOUBLE       NOT NULL,
    Kit             VARCHAR(32),
    FOREIGN KEY(Kit)
        REFERENCES Kits(Name)
        ON DELETE SET NULL
        ON UPDATE CASCADE
);
-- # &
INSERT INTO Arenas_new
SELECT * FROM Arenas;
-- # &
ALTER TABLE Arenas RENAME TO Arenas_old;
-- # &
ALTER TABLE Arenas_new RENAME TO Arenas;
-- # &
DROP TABLE IF EXISTS Arenas_old;
-- # &
RELEASE migrate_arena_fk;
-- # &
PRAGMA foreign_keys = ON;
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