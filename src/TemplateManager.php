<?php

class TemplateManager
{
    public function getTemplateComputed(Template $tpl, Array $data)
    {
        if (!$tpl) {
            throw new \RuntimeException('no tpl given');
        }

        $replaced = clone($tpl);
        $replaced->subject = $this->computeText($replaced->subject, $data);
        $replaced->content = $this->computeText($replaced->content, $data);

        return $replaced;
    }

    private function computeText(String $text, Array $data) : String
    {
        $quote = $this->getQuote($data);

        if ($quote)
        {
            $quoteFromRepository = QuoteRepository::getInstance()->getById($quote->id);
            $siteFromRepository = SiteRepository::getInstance()->getById($quote->siteId);
            $destinationOfQuote = DestinationRepository::getInstance()->getById($quote->destinationId);
            $containsDestinationLink = $this->contains($text, '[quote:destination_link]');
            $containsDestinationName = $this->contains($text, '[quote:destination_name]');
            $containsSummaryHtml = $this->contains($text, '[quote:summary_html]');
            $containsSummary = $this->contains($text, '[quote:summary]');

            if($containsDestinationLink){
                $destination = DestinationRepository::getInstance()->getById($quote->destinationId);
                $text = str_replace(
                    '[quote:destination_link]', 
                    $siteFromRepository->url . '/' . $destination->countryName . '/quote/' . $quoteFromRepository->id, 
                    $text
                );
            } else {
                $text = str_replace('[quote:destination_link]', '', $text);
            }
            if ($containsSummaryHtml) {
                $text = str_replace(
                    '[quote:summary_html]',
                    Quote::renderHtml($quoteFromRepository),
                    $text
                );
            }
            if ($containsSummary) {
                $text = str_replace(
                    '[quote:summary]',
                    Quote::renderText($quoteFromRepository),
                    $text
                );
            }
            if ($containsDestinationName) {
                $text = str_replace(
                    '[quote:destination_name]', 
                    $destinationOfQuote->countryName, 
                    $text
                );
            }
        }
        $user = $this->getUser($data);
        if($user) {
            $containsUserFirstName = $this->contains($text, '[user:first_name]');
            if ($containsUserFirstName) {
                $text = str_replace(
                    '[user:first_name]', 
                    ucfirst(
                        mb_strtolower($user->firstname)
                    ), 
                    $text
                );
            }
        }

        return $text;
    }

    private function contains(String $text, String $tag) : bool
    {
        return strpos($text, $tag) !== false;
    }

    private function getQuote($data) : ?Quote
    {
        if (isset($data['quote']) && is_a($data['quote'], "Quote")) {
            return $data['quote'];
        }
        return null;
    }

    private function getUser($data) : User
    {
        $APPLICATION_CONTEXT = ApplicationContext::getInstance();
        if (isset($data['user']) && is_a($data['user'], "user")) {
            return $data['quote'];
        }
        return $APPLICATION_CONTEXT->getCurrentUser();
    }
}
