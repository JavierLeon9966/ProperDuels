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
VALUES (:name, :armor, :inventory)
ON DUPLICATE KEY UPDATE Armor = :armor, Inventory = :inventory;
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
)
ON DUPLICATE KEY
UPDATE
  LevelName = :levelName,
  FirstSpawnPosX = :firstSpawnPosX,
  FirstSpawnPosY = :firstSpawnPosY,
  FirstSpawnPosZ = :firstSpawnPosZ,
  SecondSpawnPosX = :secondSpawnPosX,
  SecondSpawnPosY = :secondSpawnPosY,
  SecondSpawnPosZ = :secondSpawnPosZ,
  Kit = :kit;
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
-- #  { reset
-- #    { kits
DROP TABLE Kits;
-- # &
CREATE TABLE Kits(
  Name VARCHAR(32) NOT NULL,
  Armor LONGBLOB NOT NULL,
  Inventory LONGBLOB NOT NULL,
  PRIMARY KEY(Name)
);
-- #    }
-- #    { arenas
DROP TABLE Arenas;
-- # &
CREATE TABLE Arenas(
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
-- #    }
-- #  }
-- #}