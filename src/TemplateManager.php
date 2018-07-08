<?php

class TemplateManager
{
    public function getTemplateComputed(Template $tpl, Array $data) : Template
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
        if ($quote = $this->getQuote($data))
        {
            $quoteFromRepository = QuoteRepository::getInstance()->getById($quote->id);
            $siteFromRepository = SiteRepository::getInstance()->getById($quote->siteId);
            $destinationOfQuote = DestinationRepository::getInstance()->getById($quote->destinationId);

            if($containsDestinationLink = $this->contains($text, '[quote:destination_link]')){
                $destination = DestinationRepository::getInstance()->getById($quote->destinationId);
                $text = str_replace(
                    '[quote:destination_link]', 
                    $siteFromRepository->url . '/' . $destination->countryName . '/quote/' . $quoteFromRepository->id, 
                    $text
                );
            } else {
                $text = str_replace('[quote:destination_link]', '', $text);
            }
            if ($containsSummaryHtml = $this->contains($text, '[quote:summary_html]')) {
                $text = str_replace(
                    '[quote:summary_html]',
                    Quote::renderHtml($quoteFromRepository),
                    $text
                );
            }
            if ($containsSummary = $this->contains($text, '[quote:summary]')) {
                $text = str_replace(
                    '[quote:summary]',
                    Quote::renderText($quoteFromRepository),
                    $text
                );
            }
            if ($containsDestinationName = $this->contains($text, '[quote:destination_name]')) {
                $text = str_replace(
                    '[quote:destination_name]', 
                    $destinationOfQuote->countryName, 
                    $text
                );
            }
        }
        if($user = $this->getUser($data)) {
            if ($containsUserFirstName = $this->contains($text, '[user:first_name]')) {
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