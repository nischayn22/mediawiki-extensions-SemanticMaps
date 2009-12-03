<?php

/**
 * A query printer for maps using the Open Layers API optimized for OSM
 *
 * @file SM_OSMQP.php 
 * @ingroup SMOSM
 *
 * @author Jeroen De Dauw
 */

if( !defined( 'MEDIAWIKI' ) ) {
	die( 'Not an entry point.' );
}

final class SMOSMQP extends SMMapPrinter {

	public $serviceName = MapsOSM::SERVICE_NAME;	

	/**
	 * @see SMMapPrinter::setQueryPrinterSettings()
	 *
	 */
	protected function setQueryPrinterSettings() {
		global $egMapsOSMZoom, $egMapsOSMPrefix;
		
		$this->elementNamePrefix = $egMapsOSMPrefix;
		$this->defaultZoom = $egMapsOSMZoom;	

		$this->spesificParameters = array(
			'zoom' => array(
				'default' => '', 	
			)
		);	
	}	

	/**
	 * @see SMMapPrinter::doMapServiceLoad()
	 *
	 */
	protected function doMapServiceLoad() {
		global $egOSMMapsOnThisPage;
		
		MapsOSM::addOSMDependencies($this->output);
		$egOSMMapsOnThisPage++;
		
		$this->elementNr = $egOSMMapsOnThisPage;		
	}

	/**
	 * @see SMMapPrinter::addSpecificMapHTML()
	 *
	 */
	protected function addSpecificMapHTML() {		
		global $wgJsMimeType;	
		
		$markerItems = array();
		
		foreach ($this->m_locations as $location) {
			// Create a string containing the marker JS 
			list($lat, $lon, $title, $label, $icon) = $location;

			$title = str_replace("'", "\'", $title);
			$label = str_replace("'", "\'", $label);

			$markerItems[] = "getOSMMarkerData($lon, $lat, '$title', '$label', '$icon')";
		}

		$markersString = implode(',', $markerItems);		
		
		$controlItems = MapsMapper::createJSItemsString(explode(',', $this->controls));
		
		$this->output .= <<<EOT
			<script type='$wgJsMimeType'>slippymaps['$this->mapName'] = new slippymap_map('$this->mapName', {
				mode: 'osm-wm',
				layer: 'osm-like',
				locale: '$this->lang',				
				lat: $this->centre_lat,
				lon: $this->centre_lon,
				zoom: $this->zoom,
				width: $this->width,
				height: $this->height,
				markers: [$markersString],
				controls: [$controlItems]
			});</script>
		
				<!-- map div -->
				<div id='$this->mapName' class='map' style='width:{$this->width}px; height:{$this->height}px;'>
					<script type='$wgJsMimeType'>slippymaps['$this->mapName'].init();</script>
				<!-- /map div -->
				</div>
EOT;
	}		

}