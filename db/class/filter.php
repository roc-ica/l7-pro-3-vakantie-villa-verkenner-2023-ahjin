<?php

use PhpParser\Node\Expr\Cast\Double;
use PhpParser\Node\Expr\List_;

class Filter {
    private float $minPrice;
    private array $woningFaciliteiten;
    private array $liggingEigenschappen;
    private float $minOppervlakte;
    private array $eigenschappen;
    private string $zoekTerm;

    public function __construct(
        float $minPrice = 0, 
        array $woningFaciliteiten = [], 
        array $liggingEigenschappen = [],     
        float $minOppervlakte = 0, 
        array $eigenschappen = [], 
        string $zoekTerm = ''
    ) {
        $this->minPrice = $minPrice;
        $this->woningFaciliteiten = $woningFaciliteiten; 
        $this->liggingEigenschappen = $liggingEigenschappen;
        $this->minOppervlakte = $minOppervlakte;
        $this->eigenschappen = $eigenschappen;
        $this->zoekTerm = $zoekTerm;
    }

}