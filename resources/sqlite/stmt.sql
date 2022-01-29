-- #!sqlite
-- #{ properduels
-- #  { init
-- #    { kits
CREATE TABLE IF NOT EXISTS Kits(
  Name VARCHAR NOT NULL,
  Kit VARCHAR NOT NULL,
  PRIMARY KEY(Name)
);
-- #    }
-- #    { arenas
CREATE TABLE IF NOT EXISTS Arenas(
  Name VARCHAR NOT NULL,
  Arena VARCHAR NOT NULL,
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
INSERT OR REPLACE INTO Kits(Name, Kit)
VALUES (:name, :kit);
-- #    }
-- #    { arena
-- #      :name string
-- #      :arena string
INSERT OR REPLACE INTO Arenas(Name, Arena)
VALUES (:name, :arena);
-- #    }
-- #  }
-- #  { delete
-- #    { kit
-- #      :name string
DELETE FROM Kits
WHERE Name =:name
-- #    }
-- #    { arena
-- #      :name string
DELETE FROM Arenas
WHERE Name =:name
-- #    }
-- #  }
-- #}