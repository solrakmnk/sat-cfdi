<?php

namespace Blacktrue;

use InvalidArgumentException;

/**
 * Class ValidarCfdi.
 */
class ValidarCfdi
{
    const URL_IMAGEN_SAT = 'https://verificacfdi.facturaelectronica.sat.gob.mx/?ctl00%24ScriptManager1=ctl00%24MainContent%24UpnlBusqueda%7Cctl00%24MainContent%24BtnBusqueda&__EVENTTARGET=&__EVENTARGUMENT=&__VIEWSTATE=XSYWXINYzkqMyAg7BU0bVxd6Ah5zsaE1uO4d5fW6ZGKJvJ%2B%2B3Xj2DaVyi14esMzcNC557M%2FmvGLtRk3KvyUp8DPxwURM%2F%2FysqRQYJ8VX9refDcD2vTnVAew%2Bghu86SpuICsoIJ9BdRuH7jFciEAV11FozYCUv1YPL10aIY2Qx%2FrITOdm7RvaK0mfT153MdODL9uKEvCeMiDf7iAJ%2Fi65mbyIhIyIDe4EFrexUxIm5C7%2FEGHjoUPwiO8u4ufmn%2By%2B056EHK0cPMqaqB1NqyXLkxu%2Ft6u0KN6b5pEDFw6mcQMqz%2Bgk29RiGjmx86h0GAu%2FyRiIdNisZnIIARbnnozrZOYN8llULxw7gPnyiWYm6mun6KSH&__VIEWSTATEGENERATOR=CA0B0334&__VIEWSTATEENCRYPTED=&ctl00%24MainContent%24TxtUUID=<%uuid%>&ctl00%24MainContent%24TxtRfcEmisor=<%rfcEmisor%>&ctl00%24MainContent%24TxtRfcReceptor=<%rfcReceptor%>&ctl00%24MainContent%24TxTCaptchaNumbers=66740&__ASYNCPOST=true&ctl00%24MainContent%24BtnBusqueda=Verificar%20CFDI';

    /**
     * @var array
     */
    protected $params = [];

    /**
     * @var mixed
     */
    protected $path;

    /**
     * @var
     */
    protected $url;

    /**
     * ValidarCfdi constructor.
     *
     * @param array $params
     */
    public function __construct(array $params = [])
    {
        $this->params = $params;

        if (!isset($this->params['rfcEmisor'])) {
            throw new InvalidArgumentException('RFC Emisor es requerido');
        }
        if (!isset($this->params['rfcReceptor'])) {
            throw new InvalidArgumentException('RFC Receptor es requerido');
        }
        if (!isset($this->params['importe'])) {
            throw new InvalidArgumentException('Importe es requerido');
        }
        if (!isset($this->params['uuid'])) {
            throw new InvalidArgumentException('UUID es requerido');
        }
    }

    /**
     * @param $path
     *
     * @throws InvalidArgumentException
     *
     * @return $this
     */
    public function setPath($path)
    {
        if (!file_exists($path)) {
            throw new InvalidArgumentException('El directorio especificado no existe');
        }

        $this->path = $path;

        return $this;
    }

    /**
     * @return array
     */
    public function validar()
    {
        try {
            $rfc_emisor = utf8_encode(@$this->params['rfcEmisor']);
            $rfc_receptor = utf8_encode(@$this->params['rfcReceptor']);
            $impo = (double) $this->params['importe'];
            $impo = sprintf('%.6f', $impo);
            $impo = str_pad($impo, 17, '0', STR_PAD_LEFT);
            $uuid = $this->params['uuid'];

            $this->generateUrl($rfc_emisor, $rfc_receptor, $uuid);

            $dataValidation = DataHtml::getData($this->url);

            return [
                'success' => true,
                'codeSat' => $dataValidation['message'],
                'estate' => $dataValidation['estate'],
                'fechaCancelacion' => $dataValidation['fechaCancelacion'],
                'img' => $this->getImg(),
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * @param $rfcEmisor
     * @param $rfcReceptor
     * @param $uuid
     */
    private function generateUrl($rfcEmisor, $rfcReceptor, $uuid)
    {
        $this->url = str_replace([
            '<%rfcEmisor%>',
            '<%rfcReceptor%>',
            '<%uuid%>',
        ], [
            $rfcEmisor,
            $rfcReceptor,
            $uuid,
        ], self::URL_IMAGEN_SAT);
    }

    /**
     * @return mixed
     */
    private function getImg()
    {
        $fileName = CapturaImagen::capturar($this->url, $this->path);

        return base64_encode(@file_get_contents($fileName));
    }
}
