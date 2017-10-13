<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005-2011 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//      http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

class Time
{
    protected function strftime($format, $date)
    {
        return strftime($format, is_numeric($date) ? $date : strtotime($date));
    }

    public static function year($date)
    {
        return (int) static::strftime('%Y', $date);
    }

    public static function yearShort($date)
    {
        return (int) static::strftime('%g', $date);
    }

    public static function month($date)
    {
        return [
            1 => _('enero'),
            2 => _('febrero'),
            3 => _('marzo'),
            4 => _('abril'),
            5 => _('mayo'),
            6 => _('junio'),
            7 => _('julio'),
            8 => _('agosto'),
            9 => _('septiembre'),
            10 => _('octubre'),
            11 => _('noviembre'),
            12 => _('diciembre'),
        ][(int) static::strftime('%m', $date)];
    }

    public static function monthSort($date)
    {
        return substr(static::month($date), 0, 3);
    }

    public static function day($date)
    {
        return [
            1 => _('lunes'),
            2 => _('martes'),
            3 => _('miércoles'),
            4 => _('jueves'),
            5 => _('viernes'),
            6 => _('sábado'),
            7 => _('domingo'),
        ][(int) static::strftime('%u', $date)];
    }

    public static function dayShort($date)
    {
        return substr(static::day($date), 0, 3);
    }

    public static function hour($date)
    {
        return static::strftime('%R', $date);
    }

    public static function dayMonthSortHour($date)
    {
        return static::strftime('%e', $date).'/'.static::monthSort($date).' - '.static::hour($date).'h';
    }

    public static function diff($from, $now = 0)
    {
        global $globals;

        if (!preg_match('/^[0-9]+$/', $from)) {
            $from = strtotime($from);
        }

        if (empty($now)) {
            $now = $globals['now'];
        }

        $diff = $now - $from;
        $days = intval($diff / 86400);

        $diff = $diff % 86400;
        $hours = intval($diff / 3600);

        $diff = $diff % 3600;
        $minutes = intval($diff / 60);

        $secs = $diff % 60;

        if ($days > 1) {
            $txt = $days.' '._('días');
        } elseif ($days === 1) {
            $txt = $days.' '._('día');
        } else {
            $txt = '';
        }

        if ($hours > 1) {
            $txt .= ' '.$hours.' '._('horas');
        } elseif ($hours === 1) {
            $txt .= ' '.$hours.' '._('hora');
        }

        if ($minutes > 1) {
            $txt .= ' '.$minutes.' '._('minutos');
        } elseif ($minutes === 1) {
            $txt .= ' '.$minutes.' '._('minuto');
        }

        if ($txt) {
            return trim($txt);
        }

        if ($secs < 5) {
            return _('nada');
        }

        return $secs.' '._('segundos');
    }

    public static function leftTo($date)
    {
        $interval = date_create('now')->diff(date_create($date));

        if (($interval->d >= 3) || (($interval->d > 1) && ($interval->h === 0))) {
            return sprintf(_('Faltan %s días'), $interval->d);
        }

        if ($interval->d > 1) {
            return sprintf(_('Faltan %s días y %s horas'), $interval->d, $interval->h);
        }

        if (($interval->d === 1) && ($interval->h === 0)) {
            return _('Falta 1 día');
        }

        if ($interval->d === 1) {
            return sprintf(_('Falta 1 día y %s horas'), $interval->h);
        }

        if (($interval->h > 1) && ($interval->i === 0)) {
            return sprintf(_('Faltan %s horas'), $interval->h);
        }

        if ($interval->h > 1) {
            return sprintf(_('Faltan %s horas y %s minutos'), $interval->h, $interval->i);
        }

        if (($interval->h === 1) && ($interval->i === 0)) {
            return _('Falta 1 hora');
        }

        if ($interval->h === 1) {
            return sprintf(_('Falta 1 hora y %s minutos'), $interval->i);
        }

        if ($interval->i > 1) {
            return sprintf(_('Faltan %s minutos'), $interval->i);
        }

        if ($interval->i === 1) {
            return _('Falta 1 minuto');
        }

        return sprintf(_('Faltan %s segundos'), $interval->s);
    }
}
