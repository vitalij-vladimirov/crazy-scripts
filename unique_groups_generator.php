<?php

/*
 * This script groups people to unique groups where same two or more peaople will never meet more than once.
 * 1. Add as many people as you need to PEOPLE constant.
 * 2. Set GROUP_SIZE to any number you need (number should not exceed people count).
 * 3. Execute and get list of groups ordered from biggest to smallest sizes.
 *
 * Can be tested in sandbox here: https://onlinephp.io/c/a5997
*/
Class Grouping
{
    private const PEOPLE = [
        'Jim Beam',
        'Jack Daniels',
        'Tullamore Dew',
        'Captain Morgan',
        'William Peel',
        'Jameson',
        'Hennesy',
        'Remy Martin',
        'Louis Royer',
        'Ron Abuelo',
        'El Ron Prohibido',
        'Rooster Rojo',
    ];

    private const GROUP_SIZE = 4;

    private array $pairedPeople = [];
    private array $fullyPaired = [];
    private array $groups = [];
    private array $groupsOfGroups = [];

    public function group(): void
    {
        do {
            $this->groupRandomPeople();
        } while (!$this->areAllPeoplePaired());

        $this->orderGroupsByTotals();
        
        do {
        	$this->groupGroups();
        } while (count($this->groups) > 0);

        $this->printGroupsOfGroups();
    }

    private function groupRandomPeople(): void
    {
    	$people = self::PEOPLE;
    	shuffle($people);
    	
        foreach ($people as $human) {
            if (array_key_exists($human, $this->fullyPaired) && count($this->pairedPeople[$human]) === count(self::PEOPLE) - 1) {
                $fullyPaired[$human] = true;

                continue;
            }

            $group = [];
            foreach (self::PEOPLE as $pairHuman) {
                if (count($group) === self::GROUP_SIZE) {
                    break;
                }

                if ($human === $pairHuman) {
                    continue;
                }

                if (
                    $this->arePeoplePaired($human, [$pairHuman])
                    || $this->arePeoplePaired($human, $group)
                    || $this->arePeoplePaired($pairHuman, $group)
                ) {
                    continue;
                }

                if (count($group) === 0) {
                    $group[] = $human;
                }

                $group[] = $pairHuman;
            }

            if (count($group) > 0) {
                $this->groups[] = $group;
                $this->pairPeople($group);
            }
        }
    }

    private function areAllPeoplePaired(): bool
    {
        $unpairedPeople = 0;
        foreach ($this->pairedPeople as $homan => $pairedPeople) {
            if (count($pairedPeople) === count(self::PEOPLE) - 1) {
                continue;
            }

            ++$unpairedPeople;

            if ($unpairedPeople >= 2) {
                return false;
            }
        }

        return true;
    }

    private function arePeoplePaired(string $human, array $people): bool
    {
        if (count($people) === 0) {
            return false;
        }

        foreach ($people as $pairHuman) {
            if (array_key_exists($human, $this->pairedPeople) && in_array($pairHuman, $this->pairedPeople[$human])) {
                return true;
            }
        }

        return false;
    }

    private function pairPeople(array $people) {
        foreach ($people as $human) {
            foreach ($people as $pairHuman) {
                if ($human === $pairHuman) {
                    continue;
                }

                if (array_key_exists($human, $this->pairedPeople) && in_array($pairHuman, $this->pairedPeople[$human])) {
                    continue;
                }

                $this->pairedPeople[$human][] = $pairHuman;
                $this->pairedPeople[$pairHuman][] = $human;
            }
        }
    }

    private function orderGroupsByTotals(): void
    {
        $orderedGroups = [];
        foreach ($this->groups as $group) {
            $orderedGroups[count($group)][] = $group;
        }

        $mergedOrderedGroups = [];
        for ($i = self::GROUP_SIZE; $i > 1; --$i) {
            if (!array_key_exists($i, $orderedGroups) || count($orderedGroups[$i]) === 0) {
                continue;
            }

            $mergedOrderedGroups = array_merge($mergedOrderedGroups, $orderedGroups[$i]);
        }

        $this->groups = array_values($mergedOrderedGroups);
    }
    
    private function groupGroups(): void
    {
    	$index = count($this->groupsOfGroups);
    	$this->groupsOfGroups[$index] = [];
    	
    	foreach ($this->groups as $key => $group) {
    		if ($this->isAnyPersonFromGroupIncludedToGroupOfGroups($group, $this->groupsOfGroups[$index]) === true) {
    			continue;
    		}
    		
    		$this->groupsOfGroups[$index][] = $group;
    		unset($this->groups[$key]);
    	}
    	
    	if (count($this->groupsOfGroups[$index]) === 0) {
    		var_dump('fail'); exit;
    	}
    }
    
    private function isAnyPersonFromGroupIncludedToGroupOfGroups(array $group, array $groupsOfGroups): bool
    {
    	foreach ($groupsOfGroups as $subGroup) {
    		foreach ($group as $human) {
    			if (in_array($human, $subGroup, true)) {
    				return true;
    			}
    		}
    	}
    	
    	return false;
    }
    
    private function printGroupsOfGroups(): void
    {
    	foreach ($this->groupsOfGroups as $key => $groupOfGroups) {
    		echo sprintf("GROUP OF GROUPS #%d:\n", $key + 1);
    		
			$this->printGroupsList($groupOfGroups, true);
    	}
    }

    private function printGroupsList(array $groups = null, bool $isSubGroup = false): void
    {
    	$groups ??= $this->groups;
    	
    	foreach ($groups as $key => $group) {
        	if ($isSubGroup === true) {
        		echo '  ';
        	}
        	
            echo sprintf("GROUP #%d:\n", $key + 1);

            foreach ($group as $human) {
            	if ($isSubGroup === true) {
	        		echo '  ';
	        	}
	        	
                echo sprintf("- %s\n", $human);
            }

            echo "\n";
        }
    }
}

(new Grouping())->group();
