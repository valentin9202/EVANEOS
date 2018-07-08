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

        $quote = $this->getQuote($data['quote']);

        if ($quote)
        {
            $_quoteFromRepository = QuoteRepository::getInstance()->getById($quote->id);
            $_siteFromRepository = SiteRepository::getInstance()->getById($quote->siteId);
            $destinationOfQuote = DestinationRepository::getInstance()->getById($quote->destinationId);
            $containsDestinationLink = $this->contains($text, '[quote:destination_link]');
            $containsDestinationName = $this->contains($text, '[quote:destination_name]');
            $containsSummaryHtml = $this->contains($text, '[quote:summary_html]');
            $containsSummary = $this->contains($text, '[quote:summary]');

            if($containsDestinationLink){
                $destination = DestinationRepository::getInstance()->getById($quote->destinationId);
                $text = str_replace(
                    '[quote:destination_link]', 
                    $_siteFromRepository->url . '/' . $destination->countryName . '/quote/' . $_quoteFromRepository->id, 
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

        $_user  = (isset($data['user'])  and ($data['user']  instanceof User))  ? $data['user']  : $APPLICATION_CONTEXT->getCurrentUser();
        if($_user) {
            $containsUserFirstName = $this->contains($text, '[user:first_name]');
            if ($containsUserFirstName) {
                $text = str_replace(
                    '[user:first_name]', 
                    ucfirst(
                        mb_strtolower($_user->firstname)
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

    private function getQuote($quote)
    {
        if ($quote && is_a($quote, "Quote")) {
            return $quote;
        }
        return null;
    }
}
