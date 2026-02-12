<?php

namespace App\Controllers;

use App\Models\UserModel;

class LocaleController extends BaseController
{
    public function switch()
    {
        $locale = $this->request->getPost('locale');
        $supported = config('App')->supportedLocales;

        if (! in_array($locale, $supported, true)) {
            $locale = config('App')->defaultLocale;
        }

        session()->set('locale', $locale);

        // If user is logged in, persist to database
        $userId = session()->get('user_id');
        if ($userId) {
            (new UserModel())->update($userId, ['locale' => $locale]);
        }

        return redirect()->back();
    }
}
