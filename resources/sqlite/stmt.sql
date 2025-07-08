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
ORDER BY RANDOM()
LIMIT 1;
-- #    }
-- #    { kit
SELECT * FROM Kits
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
-- #}