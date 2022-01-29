-- #!mysql
-- #{ properduels
-- #  { init
-- #    { kits
CREATE TABLE IF NOT EXISTS Kits(
  Name VARCHAR(32) NOT NULL,
  Kit TEXT NOT NULL,
  PRIMARY KEY(Name)
);
-- #    }
-- #    { arenas
CREATE TABLE IF NOT EXISTS Arenas(
  Name VARCHAR(32) NOT NULL,
  Arena TEXT NOT NULL,
  PRIMARY KEY(Name)
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
-- #      :kit string
INSERT INTO Kits(Name, Kit)
VALUES (:name, :kit)
ON DUPLICATE KEY UPDATE Kit = :kit;
-- #    }
-- #    { arena
-- #      :name string
-- #      :arena string
INSERT INTO Arenas(Name, Arena)
VALUES (:name, :arena)
ON DUPLICATE KEY UPDATE Kit = :kit;
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
-- #}