<?php

namespace Habitacion;

class habitacion implements \JsonSerializable {
    
    protected $id;
    protected $m2;
    protected $ventana;
    protected $tipo_habitacion;
    protected $descripcion;
    protected $servicio_limpieza;
    protected $internet;
    protected $precio;
    protected $servicios = array();
    protected $imagenes = array();
    
    function __construct($id, $m2, $ventana, $tipo_habitacion, $descripcion, $servicio_limpieza, $internet, $precio, $servicios, $imagenes) {
        $this->id = $id;
        $this->m2 = $m2;
        $this->ventana = $ventana;
        $this->tipo_habitacion = $tipo_habitacion;
        $this->descripcion = $descripcion;
        $this->servicio_limpieza = $servicio_limpieza;
        $this->internet = $internet;
        $this->precio = $precio;
        $this->servicios = $servicios;
        $this->imagenes = $imagenes;
    }
    
    public function __get($property){
    if(property_exists($this, $property)) {
        return $this->$property;
    }
}

    public function jsonSerialize() {
        return 
        [
            'id'   => $this->id,
            'm2' => $this->m2,
            'ventana'   => $this->ventana,
            'tipo_habitacion' => $this->tipo_habitacion,
            'descripcion' => $this->descripcion,
            'servicio_limpieza'   => $this->servicio_limpieza,
            'internet' => $this->internet,
            'precio' => $this->precio,
            'servicios' => $this->servicios,
            'imagenes' => $this->imagenes   
        ];
    }

    
}

