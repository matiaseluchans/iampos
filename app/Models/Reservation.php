<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Scopes\TenantScope;
use App\Http\Traits\AuditModelTrait;

class Reservation extends Model
{
    use HasFactory, SoftDeletes, AuditModelTrait;

    protected $fillable = [
        'customer_id',
        'service_type_id',
        'resource_id',
        'start_time',
        'end_time',
        'time_units',
        'required_capacity',
        'status',
        'participants_count',
        'features', // Campo JSON para atributos dinámicos
        'customer_reference',
        'special_requirements',
        'cancellation_reason',
        'total_price',
        'notes',
        'created_by',
        'last_modified_by',
        'deleted_by',
        'tenant_id',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'time_units' => 'integer',
        'required_capacity' => 'integer',
        'participants_count' => 'integer',
        'features' => 'array', // Cast automático a array
        'total_price' => 'decimal:2'
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_COMPLETED = 'completed';
    const STATUS_NO_SHOW = 'no_show';

    // Constantes para tipos de servicio (para referencia)
    const SERVICE_TYPE_PET = 'pet';
    const SERVICE_TYPE_HOTEL = 'hotel';
    const SERVICE_TYPE_PARKING = 'parking';
    const SERVICE_TYPE_BEAUTY = 'beauty';
    const SERVICE_TYPE_MEDICAL = 'medical';

    // Constantes para keys comunes en features
    const FEATURE_PET_NAME = 'pet_name';
    const FEATURE_PET_BREED = 'pet_breed';
    const FEATURE_PET_WEIGHT = 'pet_weight';
    const FEATURE_PET_AGE = 'pet_age';
    const FEATURE_PET_GENDER = 'pet_gender';
    const FEATURE_VEHICLE_PLATE = 'vehicle_plate';
    const FEATURE_VEHICLE_MODEL = 'vehicle_model';
    const FEATURE_VEHICLE_COLOR = 'vehicle_color';
    const FEATURE_VEHICLE_TYPE = 'vehicle_type';
    const FEATURE_ADULTS_COUNT = 'adults_count';
    const FEATURE_CHILDREN_COUNT = 'children_count';
    const FEATURE_ROOM_PREFERENCE = 'room_preference';

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);
    }

    public static function getStatuses()
    {
        return [
            self::STATUS_PENDING,
            self::STATUS_CONFIRMED,
            self::STATUS_CANCELLED,
            self::STATUS_COMPLETED,
            self::STATUS_NO_SHOW
        ];
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function serviceType()
    {
        return $this->belongsTo(ServiceType::class);
    }

    public function resource()
    {
        return $this->belongsTo(Resource::class);
    }

    public function quotations()
    {
        return $this->hasMany(Quotation::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('start_time', '>', now())
                    ->where('status', self::STATUS_CONFIRMED);
    }

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_CONFIRMED)
                    ->where('start_time', '<=', now())
                    ->where('end_time', '>=', now());
    }

    public function scopePast($query)
    {
        return $query->where('end_time', '<', now());
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', self::STATUS_CANCELLED);
    }

    public function generateQuotation()
    {
        $pricingService = app(\App\Services\PricingService::class);
        $totalPrice = $pricingService->calculatePrice($this);
        
        $quotation = $this->quotations()->create([
            'base_price' => $this->serviceType->base_price,
            'tax_amount' => $totalPrice * 0.21, // 21% IVA
            'discount_amount' => 0,
            'total_price' => $totalPrice * 1.21,
            'valid_until' => now()->addHours(24),
            'status' => Quotation::STATUS_DRAFT
        ]);

        $this->update(['total_price' => $totalPrice]);

        return $quotation;
    }

    public function cancel($reason = null)
    {
        $this->update([
            'status' => self::STATUS_CANCELLED,
            'cancellation_reason' => $reason
        ]);

        // Liberar capacidad del recurso
        if ($this->resource && $this->resource->is_shared) {
            $this->resource->updateCurrentUsage();
        }
    }

    public function confirm()
    {
        $this->update(['status' => self::STATUS_CONFIRMED]);

        // Actualizar capacidad del recurso
        if ($this->resource && $this->resource->is_shared) {
            $this->resource->updateCurrentUsage();
        }
    }

    public function calculateDuration()
    {
        $start = Carbon::parse($this->start_time);
        $end = Carbon::parse($this->end_time);
        return $end->diffInMinutes($start);
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    // MÉTODOS PARA MANEJO DE FEATURES

    /**
     * Obtener un feature específico
     */
    public function getFeature(string $key, $default = null)
    {
        return Arr::get($this->features, $key, $default);
    }

    /**
     * Establecer un feature
     */
    public function setFeature(string $key, $value)
    {
        $features = $this->features ?? [];
        Arr::set($features, $key, $value);
        $this->features = $features;
        return $this;
    }

    /**
     * Establecer múltiples features
     */
    public function setFeatures(array $features)
    {
        foreach ($features as $key => $value) {
            $this->setFeature($key, $value);
        }
        return $this;
    }

    /**
     * Remover un feature
     */
    public function removeFeature(string $key)
    {
        $features = $this->features ?? [];
        Arr::forget($features, $key);
        $this->features = $features;
        return $this;
    }

    /**
     * Verificar si existe un feature
     */
    public function hasFeature(string $key): bool
    {
        return Arr::has($this->features, $key);
    }

    /**
     * Obtener todos los features
     */
    public function getFeatures(): array
    {
        return $this->features ?? [];
    }

    /**
     * Limpiar todos los features
     */
    public function clearFeatures()
    {
        $this->features = [];
        return $this;
    }

    // MÉTODOS ESPECÍFICOS POR TIPO DE SERVICIO

    /**
     * Features para servicios de mascotas
     */
    public function setPetFeatures(string $name, ?string $breed = null, ?float $weight = null, ?int $age = null, ?string $gender = null, ?string $notes = null)
    {
        return $this->setFeatures([
            self::FEATURE_PET_NAME => $name,
            self::FEATURE_PET_BREED => $breed,
            self::FEATURE_PET_WEIGHT => $weight,
            self::FEATURE_PET_AGE => $age,
            self::FEATURE_PET_GENDER => $gender,
            'pet_notes' => $notes,
        ]);
    }

    public function getPetName(): ?string
    {
        return $this->getFeature(self::FEATURE_PET_NAME);
    }

    public function getPetInfo(): array
    {
        return [
            'name' => $this->getFeature(self::FEATURE_PET_NAME),
            'breed' => $this->getFeature(self::FEATURE_PET_BREED),
            'weight' => $this->getFeature(self::FEATURE_PET_WEIGHT),
            'age' => $this->getFeature(self::FEATURE_PET_AGE),
            'gender' => $this->getFeature(self::FEATURE_PET_GENDER),
            'notes' => $this->getFeature('pet_notes'),
        ];
    }

    /**
     * Features para servicios de vehículos
     */
    public function setVehicleFeatures(string $plate, ?string $model = null, ?string $color = null, ?string $type = null)
    {
        return $this->setFeatures([
            self::FEATURE_VEHICLE_PLATE => $plate,
            self::FEATURE_VEHICLE_MODEL => $model,
            self::FEATURE_VEHICLE_COLOR => $color,
            self::FEATURE_VEHICLE_TYPE => $type,
        ]);
    }

    public function getVehicleInfo(): array
    {
        return [
            'plate' => $this->getFeature(self::FEATURE_VEHICLE_PLATE),
            'model' => $this->getFeature(self::FEATURE_VEHICLE_MODEL),
            'color' => $this->getFeature(self::FEATURE_VEHICLE_COLOR),
            'type' => $this->getFeature(self::FEATURE_VEHICLE_TYPE),
        ];
    }

    /**
     * Features para servicios de hotel
     */
    public function setHotelFeatures(int $adults, ?int $children = null, ?string $roomPreference = null)
    {
        return $this->setFeatures([
            self::FEATURE_ADULTS_COUNT => $adults,
            self::FEATURE_CHILDREN_COUNT => $children,
            self::FEATURE_ROOM_PREFERENCE => $roomPreference,
        ]);
    }

    public function getTotalGuests(): int
    {
        return ($this->getFeature(self::FEATURE_ADULTS_COUNT) ?? 0) + 
               ($this->getFeature(self::FEATURE_CHILDREN_COUNT) ?? 0);
    }

    // MÉTODOS DE DETECCIÓN DE TIPO DE SERVICIO

    public function isPetService(): bool
    {
        return $this->serviceType && $this->hasFeature(self::FEATURE_PET_NAME);
    }

    public function isParkingService(): bool
    {
        return $this->serviceType && $this->hasFeature(self::FEATURE_VEHICLE_PLATE);
    }

    public function isHotelService(): bool
    {
        return $this->serviceType && $this->hasFeature(self::FEATURE_ADULTS_COUNT);
    }

    // SCOPES PARA BÚSQUEDAS ESPECÍFICAS

    public function scopeWhereFeature($query, string $key, $operator, $value = null)
    {
        if (func_num_args() === 3) {
            $value = $operator;
            $operator = '=';
        }

        return $query->where("features->{$key}", $operator, $value);
    }

    public function scopeWhereFeatureIn($query, string $key, array $values)
    {
        return $query->whereIn("features->{$key}", $values);
    }

    public function scopeWhereFeatureNotNull($query, string $key)
    {
        return $query->whereNotNull("features->{$key}");
    }

    public function scopeWithPetFeatures($query)
    {
        return $query->whereNotNull('features->pet_name');
    }

    public function scopeWithVehicleFeatures($query)
    {
        return $query->whereNotNull('features->vehicle_plate');
    }

    // MÉTODO PARA VALIDACIÓN DE FEATURES REQUERIDOS

    public function validateRequiredFeatures(): bool
    {
        $requiredFeatures = $this->getRequiredFeaturesForService();
        
        foreach ($requiredFeatures as $feature) {
            if (!$this->hasFeature($feature)) {
                return false;
            }
        }
        
        return true;
    }

    protected function getRequiredFeaturesForService(): array
    {
        if ($this->isPetService()) {
            return [self::FEATURE_PET_NAME];
        }

        if ($this->isParkingService()) {
            return [self::FEATURE_VEHICLE_PLATE];
        }

        if ($this->isHotelService()) {
            return [self::FEATURE_ADULTS_COUNT];
        }

        return [];
    }
}