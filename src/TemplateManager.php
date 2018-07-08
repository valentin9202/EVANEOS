<?php

class TemplateManager
{
    public function getTemplateComputed(Template $tpl, array $data)
    {
        if (!$tpl) {
            throw new \RuntimeException('no tpl given');
        }

        $replaced = clone($tpl);
        $replaced->subject = $this->computeText($replaced->subject, $data);
        $replaced->content = $this->computeText($replaced->content, $data);

        return $replaced;
    }

    private function computeText($text, array $data)
    {
        $APPLICATION_CONTEXT = ApplicationContext::getInstance();

        $quote = (isset($data['quote']) and $data['quote'] instanceof Quote) ? $data['quote'] : null;

        if ($quote)
        {
            $_quoteFromRepository = QuoteRepository::getInstance()->getById($quote->id);
            $usefulObject = SiteRepository::getInstance()->getById($quote->siteId);
            $destinationOfQuote = DestinationRepository::getInstance()->getById($quote->destinationId);
            $containsDestinationLink = $this->contains($text, '[quote:destination_link]');
            $containsDestinationName = $this->contains($text, '[quote:destination_name]');
            $containsSummaryHtml = $this->contains($text, '[quote:summary_html]');
            $containsSummary = $this->contains($text, '[quote:summary]');

            if($containsDestinationLink){
                $destination = DestinationRepository::getInstance()->getById($quote->destinationId);
                $text = str_replace(
                    '[quote:destination_link]', 
                    $usefulObject->url . '/' . $destination->countryName . '/quote/' . $_quoteFromRepository->id, 
                    $text
                );
            } else {
                $text = str_replace('[quote:destination_link]', '', $text);
            }
            if ($containsSummaryHtml) {
                $text = str_replace(
                    '[quote:summary_html]',
                    Quote::renderHtml($_quoteFromRepository),
                    $text
                );
            }
            if ($containsSummary) {
                $text = str_replace(
                    '[quote:summary]',
                    Quote::renderText($_quoteFromRepository),
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
        
        /*
         * USER
         * [user:*]
         */
        $_user  = (isset($data['user'])  and ($data['user']  instanceof User))  ? $data['user']  : $APPLICATION_CONTEXT->getCurrentUser();
        if($_user) {
            (strpos($text, '[user:first_name]') !== false) and $text = str_replace('[user:first_name]'       , ucfirst(mb_strtolower($_user->firstname)), $text);
        }

        return $text;
    }

    private function contains(String $text, String $tag) : bool
    {
        return str_pos($text, $tag) !== false;
    }
}
