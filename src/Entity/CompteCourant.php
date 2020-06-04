<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class CompteCourant extends Compte
{
    /**
     * @ORM\Column(type="float")
     */
    private $decouvert;

    public function getDecouvert(): ?float
    {
        return $this->decouvert;
    }

    public function setDecouvert(float $decouvert): self
    {
        $this->decouvert = $decouvert;

        return $this;
    }

    function __construct() {
        $argv = func_get_args();
        switch( func_num_args() ) {
            case 1:
                self::__construct1();
                break;
            case 2:
                self::__construct2( $argv[0], $argv[1], $argv[2], $argv[3]);
         }
    }

    public function __construct1(){
        parent::__construct();
    }

    public function __construct2($codeCompte,$dateCreation,$solde,$decouvert){
        parent::__construct($codeCompte,$dateCreation,$solde);
        $this->$decouvert=$decouvert;
    }

}
?>