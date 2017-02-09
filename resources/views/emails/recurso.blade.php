<html>
  <head>
    <meta charset="utf-8">
    <meta name="description" content="CIUM">
    <meta name="author" content="SSA">
    <meta name="keyword" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>CIUM</title>
    <style type="text/css">
      <?php require_once(public_path()."/css/bootstrap.css") ?>
    </style>
  </head>
  <body>
    <div class="imprimir" > 
        
      <table  cellspacing="0" class="Tabla" align="right" style="margin-bottom:1em">
          <tr style="background:#ddd">
            <th colspan="4">EVALUACION RECURSO</th>
            <th>FOLIO</th>          
          </tr>
          <tr>
            <th>FECHA EVALUACION:</th>
            <td>{{ $evaluacion->fechaEvaluacion}}</td>
            <th>ESTADO:</th>
            <td style="background:#{{ $evaluacion->cerrado  ? 'FF3C3C' : '7BE15E' }}">{{ $evaluacion->cerrado  ? 'Cerrado' : 'Abierto' }}</td>
            <th>{{ $evaluacion->id}}</th>
            
          </tr>        
        </table>
        
        <table width="100%" cellspacing="0" class="Tabla">
          <caption>DATOS UNIDAD</caption>
            <tr>
              <td><strong>CLUES</strong></td>
              <td>{{ $evaluacion->clues}}</td>
              <td><strong>NIVEL CONE</strong></td>
              <td>{{ $evaluacion->nivelCone}}</td>
            </tr>
            <tr>
              <td><strong>NOMBRE</strong></td>
              <td colspan="3">{{ $evaluacion->nombre}}</td>
            </tr>
            <tr>
              <td><strong>JURISDICCION</strong></td>
              <td> {{ $evaluacion->jurisdiccion}}</td>
              <td><strong>MUNICIPIO</strong></td>
              <td>{{ $evaluacion->municipio}}</td>
            </tr>
            <tr>
              <td><strong>ZONA</strong></td>
              <td>{{ $evaluacion->zona}}</td>
              <td><strong>TIPOLOGIA</strong></td>
              <td>{{ $evaluacion->tipologia}} | {{ $evaluacion->tipoUnidad}}</td>
            </tr>
            <tr>
              <td><strong>DOMICILIO</strong></td>
              <td colspan="3"> {{ $evaluacion->domicilio}}  <strong>CP: </strong> {{ $evaluacion->codigoPostal }}</td>
            </tr>
          </table>
        <br>       
          <table width="100%" cellspacing="0" cellpadding="4" class="Tabla">  
            <caption>RESULTADOS EVALUCAION</caption>   
            <thead>       
              <tr>            
                <th rowspan="2" colspan="3">INDICADOR</th>
                <th colspan="3" style="text-align: center;">CRITERIOS</th>
                <th rowspan="2">PORCENTAJE</th>
                <th rowspan="2">CUMPLE</th>
              </tr>
              <tr>
                <th width="1%">TOTAL</th>
                <th width="1%">APROBADO</th>
                <th width="1%">N/APROBADO</th>
              </tr>
            </thead>  <!-- TODO: ng-init -->
            <tbody >  
            @FOREACH($estadistica as $value)  <?php $value->indicadores = (object) $value->indicadores; ?>  
              <tr >   
                  <td width="1%" align="center">
                    <span ng-init="$value->indicadores->totalNoAplica>0 ? sumarNoAplica() : ''" class="img-circle" style="background: {{ $value->color }}; display:block; height:25px; width:25px;">
                    </span>
                  </td>               
                  <th width="1%" align="center">{{ $value->codigo }}</th>
                  <td>{{ $value->nombre }}</td>
                  <td align="center">{{ $value->indicadores->totalCriterios }}</td>
                  <td align="center">{{ $value->indicadores->totalAprobados }}</td>
                  <td align="center">{{ $value->indicadores->totalCriterios - ($value->indicadores->totalAprobados + $value->indicadores->totalNoAplica) }}</td>                  
                  <td>
                    <div class="progress" >
                        <div class="progress-bar progress-bar-striped active progress-bar-default" role="progressbar"
                        aria-valuenow="{{ $value->indicadores->totalPorciento }}" aria-valuemin="0" aria-valuemax="100" style="width:{{ $value->indicadores->totalPorciento }}%; background:{{ $value->indicadores->totalColor }}">
                          {{ $value->indicadores->totalPorciento }}%
                        </div></div>			
                </td> 
                <td width="1%" align="center" style="background:#{{ $value->indicadores->totalCriterios != ($value->indicadores->totalAprobados + $value->indicadores->totalNoAplica)  ? 'FF3C3C' : '7BE15E' }}">
                  {{ $value->indicadores->totalCriterios == ($value->indicadores->totalAprobados + $value->indicadores->totalNoAplica) ? 'Si' : 'No' }}
                </td>       
              </tr>
            @ENDFOREACH
            </tbody>
          </table>     
            <h3 style="text-align:center; font-size:1em;" class="h3-caption ">DETALLES EVALUACION

      </h3>
      <div >
        @FOREACH($indicadores as $criterios) <?php  $criterios = (object) $criterios; ?>
        <div style="border-bottom: 2px solid #999;  padding-bottom:3em; margin-bottom:3em;">                  
         <table width="100%" class="Tabla" style="margin-bottom:0.5em">

        
            <caption style="font-size:1.3em">INDICADOR</caption>
            <tbody>
              <tr>
                <th>Código</th>
                <th style="text-align:left;"><span class="img-circle" style="background: {{ $criterios->indicador->color }}; display:block; height:1em; width:1em; float:left; margin-right:.5em"></span> {{ $criterios->indicador->codigo }}</th>
              </tr>
              <tr>
                <th>Nombre</th>
                <th style="text-align:left;">{{ $criterios->indicador->nombre }}</th>
              </tr>
            </tbody>
          </table>
          @FOREACH($criterios as $key => $value)
          @IF($key != "indicador" && $key != "hallazgo")
          <div style="margin-bottom:0.5em">
            <table width="100%" class="Tabla" style="border:0" cellspacing="0"  >                
                <thead>
                  <tr>
                    <th align="left">{{$key}}</th>
                    <th colspan="2" align="center">APROBADO</th>
                  </tr>
                  <tr>
                    <th align="left">CRITERIO</th>
                    <th >SI</th>
                    <th >NO</th>
                  </tr>
                </thead>
                <tbody>
                  @FOREACH($value as $c) 
                  <tr >
                    <td width="90%">{{ $c->criterio  }}</td>
                      <td width="1%" style="background-color: {{ $c->aprobado == 1 ? '#7BE15E'  : '' }}; color:#000; font-weight:bold;">                       
                      </td>
                      <td width="1%" style="background-color: {{ $c->aprobado == 0 ? '#FF3C3C' : '' }}; color:#000; font-weight:bold;">                        
                      </td>
                    </tr>
                  @ENDFOREACH
                </tbody>
              </table>
            </div>
            @ENDIF
            @ENDFOREACH
            @IF(isset($criterios->hallazgo))
            <br>
            <table class="Tabla" width="100%">
              <thead>
                <tr>
                  <th colspan="2">HALLAZGO</th>
                </tr>            
              </thead>
              <tbody>
              <tr>
                <th align="left" width="1%">DESCRIPCION</th>
                <td>{{ $criterios->hallazgo->descripcion}}</td>
              </tr>
              <tr>
                <th align="left" width="1%">ACCION</th>     
                <td>{{ $criterios->hallazgo->accion}}</td>
              </tr>
              </tbody>
            </table>   
            @ENDIF
        </div>
        @ENDFOREACH
        </div>
      <br>
      <div align="center">
          <div align="center" style="border: 1px solid #666; width:40%">
            <br>
            RESPONSABLE
            <div style="margin-top:40px;"><img  style="width:30%" src="data:image/png;base64,{{ $evaluacion->firma}}"></div>
            
            <div style="padding:15px;">{{ $evaluacion->responsable}}</div>
            <div style="padding:15px;">NOMBRE FIRMA</div>
            <div align="center" style=" font-size:0.8em;border-top: 1px solid #666; width:100%;">
              <span style="padding:.5em;display:block">
                REALIZO: 
              </span>
            </div>
            <div align="center" style="font-size:0.8em; border-top: 1px solid #666; width:auto; padding:0.5em;">
                {{ $evaluacion->email}}
              
            </div>
          </div>
      </div>
    </div>
  </body>
</html>