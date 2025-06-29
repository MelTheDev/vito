<?php

namespace App\Http\Resources;

use App\Models\Database;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Database */
class DatabaseResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'server_id' => $this->server_id,
            'name' => $this->name,
            'collation' => $this->collation,
            'charset' => $this->charset,
            'status' => $this->status,
            'status_color' => Database::$statusColors[$this->status] ?? 'gray',
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
