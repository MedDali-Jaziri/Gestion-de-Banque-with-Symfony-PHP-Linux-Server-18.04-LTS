<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
 
/**
 * @ORM\Entity
 */
class CompteEpargne extends Compte
{
    /**
     * @ORM\Column(type="float")
     */
    private $taux;

    public function getTaux(): ?float
    {
        return $this->taux;
    }

    public function setTaux(float $taux): self
    {
        $this->taux = $taux;

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

    public function __construct2($codeCompte,$dateCreation,$solde,$taux){
        parent::__construct($codeCompte,$dateCreation,$solde);
        $this->$taux=$taux;
    }


}
?>