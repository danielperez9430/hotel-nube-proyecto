<?php

namespace Habitacion;

class servicios {
    
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

    
    function __toString() {
        return "<li>{Servicio: $this->nombre_servicio}</li>";
    }
}