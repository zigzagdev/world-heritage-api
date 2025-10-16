<?php

namespace App\Packages\Features\QueryUseCases\ViewModel;

use App\Packages\Features\QueryUseCases\Dto\WorldHeritageDto;

class WorldHeritageViewModel
{
    public function __construct(
        private readonly WorldHeritageDto $dto
    ) {}

    public function getImages(): array
    {
        // ここは DTO が返す配列をそのまま返す
        $images = $this->dto->getImages();
        return is_array($images) ? $images : [];
    }

    public function getId(): int { return $this->dto->getId(); }
    public function getOfficialName(): string { return $this->dto->getOfficialName(); }
    public function getName(): string { return $this->dto->getName(); }
    public function getCountry(): string { return $this->dto->getCountry(); }
    public function getRegion(): string { return $this->dto->getRegion(); }
    public function getCategory(): string { return $this->dto->getCategory(); }
    public function getYearInscribed(): int { return $this->dto->getYearInscribed(); }
    public function getLatitude(): ?float { return $this->dto->getLatitude(); }
    public function getLongitude(): ?float { return $this->dto->getLongitude(); }
    public function isEndangered(): bool { return $this->dto->isEndangered(); }
    public function getNameJp(): ?string { return $this->dto->getNameJp(); }
    public function getStateParty(): ?string { return $this->dto->getStateParty(); }
    public function getCriteria(): ?array { return $this->dto->getCriteria(); }
    public function getAreaHectares(): ?float { return $this->dto->getAreaHectares(); }
    public function getBufferZoneHectares(): ?float { return $this->dto->getBufferZoneHectares(); }
    public function getShortDescription(): ?string { return $this->dto->getShortDescription(); }
    public function getUnescoSiteUrl(): ?string { return $this->dto->getUnescoSiteUrl(); }
    public function getStatePartyCodes(): array { return $this->dto->getStatePartyCodes(); }
    public function getStatePartiesMeta(): array { return $this->dto->getStatePartiesMeta(); }

    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'official_name' => $this->getOfficialName(),
            'name' => $this->getName(),
            'country' => $this->getCountry(),
            'region' => $this->getRegion(),
            'category' => $this->getCategory(),
            'year_inscribed' => $this->getYearInscribed(),
            'latitude' => $this->getLatitude(),
            'longitude' => $this->getLongitude(),
            'is_endangered' => $this->isEndangered(),
            'name_jp' => $this->getNameJp(),
            'state_party' => $this->getStateParty(),
            'criteria' => $this->getCriteria(),
            'area_hectares' => $this->getAreaHectares(),
            'buffer_zone_hectares' => $this->getBufferZoneHectares(),
            'short_description' => $this->getShortDescription(),
            'unesco_site_url' => $this->getUnescoSiteUrl(),
            'state_party_codes' => $this->getStatePartyCodes(),
            'state_parties_meta' => $this->getStatePartiesMeta(),
            'images' => $this->getImages(),
        ];
    }
}
