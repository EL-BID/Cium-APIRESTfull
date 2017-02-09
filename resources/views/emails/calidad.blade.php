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
            <th colspan="4">EVALUACION CALIDAD</th>
            <th>FOLIO</th>          
          </tr>
          <tr>
            <th>FECHA EVALUACION:</th>
            <td>{{ $evaluacion->fechaEvaluacion }}</td>
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
        <table class="Tabla" width="100%">
            <caption>RESULTADOS EVALUACION</caption>   
            <thead >
              <tr>

                <th rowspan="2" colspan="3">INDICADOR</th>
                <th colspan="2" style="border-right: 3px solid #000 !important;">EVALUACION POR: CRITERIOs</th> 
                <th colspan="4" >Evaluación Por:EXPEDIENTE</th> 
                <th rowspan="2">CUMPLE</th>
              </tr>
              <tr >            
                <th width="1px" align="center" >TOTAL</th>
                <th width="1px" align="center" style="border-right: 3px solid #000 !important;">PORCENTAJE</th>
                 
                <th width="1px" align="center" >TOTAL</th>
                <th width="1px" align="center" >APROBADOs</th>
                <th width="1px" align="center" >N/APROBADOS</th> 
                <th width="1px" align="center" >PORCENTAJES</th>                          
              </tr> 
            </thead>
            <tbody> 
          
              @FOREACH($indicadores as $value)                                 
              <tr > 
                <td width="1%" align="center"><span class="img-circle" style="background: {{ $value->clr }}; display:block; height:1em; width:1em;"></span></td>    
                <th width="1%" align="center">{{ $value->codigo }}</th>                                   
                <td>{{ $value->indicador }}</td>
                              
                <td width="1%" align="center"  >
                  {{ $value->totalCriterio / $value->totalColumnas }}
                
                </td>
                <td style="border-right: 3px solid #000 !important;">
                    <div class="progress" >
                      <div class="progress-bar progress-bar-striped active progress-bar-default" role="progressbar"
                      aria-valuenow="{{ $value->porciento }}" aria-valuemin="0" aria-valuemax="100" style="width:{{ $value->porciento }}%; background:{{ $value->color }}">
                        {{ $value->porciento }}%
                    </div></div>
                </td> 
                <td width="1%" align="center" >
                  {{ $value->totalColumnas }}
                </td>

                <td width="1%" align="center" >
                  {{ $value->aprobado }}
                </td>
                <td width="1%" align="center" >
                  {{ $value->noAprobado }}
                </td>
                             
                <td>
                    <div class="progress" >
                      <div class="progress-bar progress-bar-striped active progress-bar-default" role="progressbar"
                      aria-valuenow="{{ ($value->aprobado /  $value->totalColumnas ) * 100 }}" aria-valuemin="0" aria-valuemax="100" style="width:{{ ($value->aprobado /  $value->totalColumnas ) * 100 }}%; background: lightgrey">
                        {{ number_format(($value->aprobado /  $value->totalColumnas ) * 100,2) }}%
                    </div></div>
                </td> 
                <td width="1%" align="center" style="background:#{{ $value->totalCriterio != $value->sumaCriterio  ? 'FF3C3C' : '7BE15E' }}">
                  {{ $value->totalCriterio == $value->sumaCriterio ? 'Si' : 'No' }}
                </td>        
              </tr>
              @ENDFOREACH
            </tbody>
          </table>
          <br>              
           
      <h3 style="text-align:center; font-size:1em;" class="h3-caption ">DETALLES EVALUACION 

      </h3>
      <div >
      @FOREACH($indicadores as $key => $indi)
        <div style="border-bottom: 2px solid #999;  padding-bottom:3em; margin-bottom:3em;">  

          <table width="100%" class="Tabla" style="margin-bottom:0.5em">
            <caption style="font-size:1.3em">INDICADOR</caption>
            <tbody>
              <tr>
                <th>Código</th>
                <th style="text-align:left;"><span class="img-circle" style="background: {{ $indi->clr }}; display:block; height:1em; width:1em; float:left; margin-right:.5em"></span> {{ $indi->codigo }}</th>
              </tr>
              <tr>
                <th>Nombre</th>
                <th style="text-align:left;">{{ $indi->indicador }}</th>
              </tr>
            </tbody>
          </table>

          <table width="100%" class="Tabla" cellpadding="0" cellspacing="0" style="margin-bottom:0.5em"> 
            
            <caption>RESUMEN</caption>
            <thead >          
              <tr> 
                <th align="right" > EXPEDIENTE</th>  
                @FOREACH($indi->columnas as $v) <?php $v = (object) $v;  ?>                              
                <td style="vertical-align:bottom;text-align:center; height:{{ count($v->expediente)/2.8 }}em;" width="1%" align="center">
                <span class="texto-vertical">{{ $v->expediente }}</span></td>
                @ENDFOREACH
              </tr>                      
            </thead>
            <tbody>                                                     
              <tr>   
                <th align="right" > PORCENTAJE</th> 
                @FOREACH($indi->columnas as $a)  <?php $a = (object) $a;  ?> 
                <td width="1%" align="center" style="background-color:{{ $a->color }};color:white; border: 1px #fff; font-size:.7em !important" >
                  {{ number_format(($a->total/($criterios[$indi->codigo]["totalCriterios"]))*100 ,2) }}% 
                </td>  
                @ENDFOREACH                                 
              </tr>
            </tbody>
          </table>  

          @FOREACH($criterios[$indi->codigo] as $k => $valor) 
          @IF($k != "" && $k != "totalCriterios")
          <?php $index = 0; $y = 0; ?>
          <table width="100%"  class="Tabla" style="margin-bottom:0.5em">
            <caption>DETALLE</caption>
            <thead >
              <tr >                                                  
                <th align="right" width="40%">NUMERO EXP</th>
                @FOREACH($indi->columnas as $v) <?php $v = (object) $v; ?>   
                <td style="vertical-align:bottom;text-align:center; height:{{ count($v->expediente)/2.8 }}em;"  width="1%" align="center"  ><span class="texto-vertical">{{ $v->expediente }}</span></td>
                @ENDFOREACH  
              </tr>
              <tr>
                <th  align="left" >{{ $k }} </th>
                @FOREACH($indi->columnas as $v)    
                <td  width="1%" align="center"  style="font-size:.7em !important" >{{ $index+1 }}</td>
                @ENDFOREACH  
              </tr>
            </thead>
            <tbody>
              @IF($valor)
                @FOREACH($valor as $c)                                
                  <tr>
                    @IF($c->nombre != "totalCriterios")   
                      <td width="40%">{{ $c->nombre  }}</td>
                      @FOREACH($indi->columnas as $a) <?php $a = (object) $a; $color = ''; if($datos[$indi->codigo][$a->expediente][$y]->aprobado) $color = '#7BE15E'; if(!$datos[$indi->codigo][$a->expediente][$y]->aprobado) $color = '#FF3C3C'; ?>      
                      <td width="1%" style=" background-color: {{ $color }}; color:#FFF; font-weight:bold;"></td>
                      @ENDFOREACH 
                      <?php  $y++;?>
                    @ENDIF                                                                               
                  </tr>
                @ENDFOREACH 
              @ENDIF
            </tbody>
          </table>
          @ENDIF
          @ENDFOREACH 
          @IF(isset($hallazgos[$indi->codigo]))
          <table width="100%" class="Tabla"  width="100%">
            <caption>HALLAZGO</caption>
            <tbody>
            <tr>
              <th align="left" width="1%">DESCRIPCION</th>
              <td>{{ $hallazgos[$indi->codigo]->descripcion}}</td>
            </tr>
            <tr>
              <th align="left" width="1%">ACCION</th>     
              <td>{{ $hallazgos[$indi->codigo]->accion}}</td>
            </tr>
            </tbody>
          </table> 
          @ENDIF
        </div>
      </div>
      @ENDFOREACH
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