<?php

namespace App\Livewire\MaterialPrice;

use App\Models\Dealer;
use App\Services\MaterialPriceService;
use App\Services\MaterialTypeService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Component;

class MaterialPriceCreate extends Component
{
    use LivewireAlert;

    public null|Collection $materialTypes;
    public null|Collection $dealers;

    public bool $is_admin = false;

    public null|int $dealer_id = 0;
    public null|int $material_type_id = 0;

    public null|string $name;
    public null|float $steel_price = 0;
    public null|float $steel_workmanship = 0;
    public null|float $steel_price_workmanship = 0;
    public null|float $steel_glue = 0;
    public null|float $steel_aplus = 1;
    public null|float $steel_standart = 1;
    public null|float $module_price = 0;
    public null|float $module_workmanship = 0;
    public null|float $module_aplus = 1;

    /**
     * List of add/edit form rules
     */
    protected $rules = [
        'dealer_id' => ['required', 'exists:dealers,id'],
        'material_type_id' => ['required', 'exists:material_types,id'],
        'name' => ['required'],
        'steel_price' => 'required',
        'steel_workmanship' => ['required'],
        'steel_price_workmanship' => ['required'],
        'steel_glue' => ['required'],
        'steel_aplus' => ['required'],
        'steel_standart' => ['required'],
        'module_price' => ['required'],
        'module_workmanship' => ['required'],
        'module_aplus' => ['required'],
    ];

    protected $messages = [
        'dealer_id.required' => 'Bayi seçiniz.',
        'dealer_id.exists' => 'Lütfen geçerli bir bayi seçiniz.',
        'material_type_id.required' => 'Maliyet türünü seçiniz.',
        'material_type_id.exists' => 'Lütfen geçerli bir maliyet türünü seçiniz.',
        'name.required' => 'Maliyet kalemi adını yazınız.',
        'steel_price.required' => 'Maliyet kalemini yazınız.',
        'steel_workmanship.required' => 'Maliyet kalemini yazınız.',
        'steel_price_workmanship.required' => 'Maliyet kalemini yazınız.',
        'steel_glue.required' => 'Maliyet kalemini yazınız.',
        'steel_aplus.required' => 'Maliyet kalemini yazınız.',
        'steel_standart.required' => 'Maliyet kalemini yazınız.',
        'module_price.required' => 'Maliyet kalemini yazınız.',
        'module_workmanship.required' => 'Maliyet kalemini yazınız.',
        'module_aplus.required' => 'Maliyet kalemini yazınız.',
    ];

    public function render()
    {
        return view('livewire.material-price.material-price-create');
    }

    public function mount(MaterialTypeService $materialTypeService, Dealer $dealer)
    {
        $this->materialTypes = $materialTypeService->all();
        $this->is_admin = Auth::user()->hasRole('admin');
        if($this->is_admin){
            $this->dealers = $dealer->query()->get();
        }
        else if(Auth::user()->hasRole('bayi')){
            $this->dealer_id = auth()->user()->id;
        }
        else {
            $this->dealer_id = auth()->user()->dealer_id??null;
        }
    }

    /**
     * store the user inputted student data in the students table
     *
     * @return void
     */
    public function store(MaterialPriceService $materialPriceService)
    {
        $this->validate();
        DB::beginTransaction();
        try {
            $material = $materialPriceService->create([
                'dealer_id' => $this->dealer_id,
                'material_type_id' => $this->material_type_id,
                'name' => $this->name,
                'steel_price' => $this->steel_price,
                'steel_workmanship' => $this->steel_workmanship,
                'steel_price_workmanship' => $this->steel_price_workmanship,
                'steel_glue' => $this->steel_glue,
                'steel_aplus' => $this->steel_aplus,
                'steel_standart' => $this->steel_standart,
                'module_price' => $this->module_price,
                'module_workmanship' => $this->module_workmanship,
                'module_aplus' => $this->module_aplus,
            ]);

            $this->dispatch('pg:eventRefresh-MaterialPriceTable')->to(MaterialPriceTable::class);
            $msg = 'Yeni maliyet kalemi oluşturuldu.';
            session()->flash('message', $msg);
            $this->alert('success', $msg, ['position' => 'center']);
            DB::commit();
            $this->reset();
        } catch (\Exception $exception) {
            $error = "Maliyet kalemi oluşturulamadı. {$exception->getMessage()}";
            session()->flash('error', $error);
            $this->alert('error', $error);
            Log::error($error);
            DB::rollBack();
        }
    }
}
