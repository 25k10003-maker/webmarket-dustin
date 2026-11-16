<?php

namespace App\Livewire\Gudang;

use App\Models\StockMutation;
use Livewire\Component;
use Livewire\WithPagination;

class StockMutations extends Component
{
    use WithPagination;

    public string $search = '';
    public string $typeFilter = 'all';
    public string $startDate = '';
    public string $endDate = '';

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedTypeFilter()
    {
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->search = '';
        $this->typeFilter = 'all';
        $this->startDate = '';
        $this->endDate = '';
        $this->resetPage();
    }

    public function render()
    {
        $query = StockMutation::with(['product', 'user'])->latest();

        if (!empty($this->search)) {
            $term = $this->search;
            $query->where(function ($q) use ($term) {
                $q->where('reference_number', 'like', "%{$term}%")
                  ->orWhere('notes', 'like', "%{$term}%")
                  ->orWhereHas('product', function ($pq) use ($term) {
                      $pq->where('name', 'like', "%{$term}%")
                         ->orWhere('barcode', 'like', "%{$term}%");
                  });
            });
        }

        if ($this->typeFilter !== 'all') {
            $query->where('type', $this->typeFilter);
        }

        if (!empty($this->startDate)) {
            $query->whereDate('created_at', '>=', $this->startDate);
        }

        if (!empty($this->endDate)) {
            $query->whereDate('created_at', '<=', $this->endDate);
        }

        $mutations = $query->paginate(15);

        return view('livewire.gudang.stock-mutations', [
            'mutations' => $mutations,
        ]);
    }
}

