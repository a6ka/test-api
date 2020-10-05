<?php

namespace app\dto;

class KeyStorageDto
{
    private string $firstKey;
    private string $secondKey;
    private string $createdAt;

    public function __construct(string $firstKey, string $secondKey, string $createdAt = null)
    {
        $this->firstKey = $firstKey;
        $this->secondKey = $secondKey;
        $this->createdAt = $createdAt ?? date('Y-m-d H:i:s');
    }

    public function getFirstKey()
    {
        return $this->firstKey;
    }

    public function getSecondKey()
    {
        return $this->secondKey;
    }

    public function getCreatedAt()
    {
        return $this->createdAt;
    }
}