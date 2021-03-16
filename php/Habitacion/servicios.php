<?php

namespace Habitacion;

class servicios implements \JsonSerializable{
    
    protected $id;
    protected $nombre_servicio;
    protected $precio_servicio;
    protected $descripcion;
    protected $disponibilidad;
    
    function __construct($id, $nombre_servicio, $precio_servicio, $descripcion, $disponibilidad) {
        $this->id = $id;
        $this->nombre_servicio = $nombre_servicio;
        $this->precio_servicio = $precio_servicio;
        $this->descripcion = $descripcion;
        $this->disponibilidad = $disponibilidad;
    }

    public function jsonSerialize() {
        return 
        [
            'id'   => $this->id,
            'nombre_servicio' => $this->nombre_servicio,
            'precio_servicio'   => $this->precio_servicio,
            'descripcion'   => $this->descripcion,
            'disponibilidad' => $this->disponibilidad
        ];
    }

}