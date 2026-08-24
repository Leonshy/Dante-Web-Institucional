<?php

namespace App\Livewire;

use App\Services\Search\SearchResult;
use App\Services\Search\SearchService;
use Illuminate\Support\Collection;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Vista reactiva del buscador interno (docs/06-frontend.md — decisión: `/buscar`
 * negocia contenido en SearchController; para HTML se embebe este componente,
 * que reutiliza el mismo SearchService ya probado en Fase 3, sin tocarlo).
 */
class SearchPage extends Component
{
    #[Url(as: 'q', history: true)]
    public string $query = '';

    public string $type = 'todo';

    public function mount(?string $query = null): void
    {
        if ($query !== null) {
            $this->query = $query;
        }
    }

    public function updatingQuery(): void
    {
        $this->resetErrorBag();
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    /** @return Collection<int, SearchResult> */
    public function getResultsProperty(): Collection
    {
        $term = trim($this->query);

        if (mb_strlen($term) < 2) {
            return collect();
        }

        $results = app(SearchService::class)->search($term);

        if ($this->type !== 'todo') {
            $results = $results->where('type', $this->type);
        }

        return $results->values();
    }

    public function render()
    {
        return view('livewire.search-page');
    }
}
