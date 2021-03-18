<?php
namespace Algo26\IdnaConvert\Test;

use Algo26\IdnaConvert\Exception\InvalidIdnVersionException;
use Algo26\IdnaConvert\ToUnicode;
use PHPUnit\Framework\TestCase;

class ToUnicodeTest extends TestCase
{
    /**
     * @dataProvider providerUtf8
     * @throws InvalidIdnVersionException
     */
    public function testDecodeUtf8($encoded, $expectDecoded)
    {
        $idnaConvert = new ToUnicode();
        $encoded = $idnaConvert->convert($encoded);

        $this->assertEquals(
            $expectDecoded,
            $encoded,
            sprintf(
                'Strings "%s" and "%s" do not match',
                $expectDecoded,
                $encoded
            )
        );
    }

    /**
     * @dataProvider providerEmailAddress
     *
     * @throws InvalidIdnVersionException
     */
    public function testDecodeEmailAddress($encoded, $expectDecoded)
    {
        $idnaConvert = new ToUnicode();
        $encoded = $idnaConvert->convertEmailAddress($encoded);

        $this->assertEquals(
            $expectDecoded,
            $encoded,
            sprintf(
                'Strings "%s" and "%s" do not match',
                $expectDecoded,
                $encoded
            )
        );
    }

    /**
     * @dataProvider providerUrl
     *
     * @throws InvalidIdnVersionException
     */
    public function testDecodeUrl($encoded, $expectDecoded)
    {
        $idnaConvert = new ToUnicode();
        $encoded = $idnaConvert->convertUrl($encoded);

        $this->assertEquals(
            $expectDecoded,
            $encoded,
            sprintf(
                'Strings "%s" and "%s" do not match',
                $expectDecoded,
                $encoded
            )
        );
    }

    /**
     * @return array
     */
    public function providerUtf8()
    {
        return [
            ['xn--mller-kva', 'müller'],
            ['xn--weienbach-i1a', 'weißenbach'],
            ['xn----9mcj9fole', 'يوم-جيد'],
            ['xn----2hckbod3a', 'יום-טוב'],
            ['xn--idndomainaouexample-owb39ane.example', 'idndomainäaöoüuexample.example'],
            ['xn--ko-eka.example', 'öko.example'],
            ['xn--6ca0bl71b4a.example', 'æšŧüø.example'],
            ['xn--4cabegsede9b0e.example', 'ìåíèäæìúíò.example'],
            ['xn--d1abegsede9b0e.example', 'мениджмънт.example'],
            ['3+1', '3+1'],
            ['www.xn--bckermller-q5a70a.example', 'www.bäckermüller.example'],
            ['xn--cfa', 'ı'],
            ['xn--ekiszlk-d1a0dy4d', 'ekşisözlük'],
            ['xn--rdetforstrrefrdselssikkerhed-znc6bz8b', 'rådetforstørrefærdselssikkerhed'],
            ['xn--kakavalc-0kb76b.example', 'kaşkavalcı.example'],
            ['xn--uxan.example', 'πι.example'],
            ['xn--ksigowo-c5a1nq1a.example', 'księgowość.example'],
            ['xn--80aebfcdsb1blidpdoq4e1i.example', 'регистрациядоменов.example'],
            ['xn--eqr31enth05q.xn--55qx5d', '国际域名.公司'],
            ['xn--1caqmypyo.example', 'áéíóöúü.example'],
            ['xn--1caqmypyo29d8i.example', 'áéíóöőúüű.example'],
            ['xn--vk1bq81c.example', '대출.example'],
            ['xn--t-mfutbzh', 'tシャツ'],
            ['www.xn--clcul3aaa2lcuc4kf.example', 'www.குண்டுபாப்பா.example'],
            ['xn--3e0b707e', '한국'],
            ['xn--xu5bx2sncw5i.example', '파티하임.example'],
            ['xn--o39aa', '가가'],
            ['xn----5gc8bsteqom5gm.xn--5dbik1ed.xn--9dbalbu5cfl', 'מילון-ראשׁי.תיבות.וקיצורים'],
            ['xn--rjajzusknak-r7a3h5b', 'írjajézuskának'],
            ['xn--q3cq3aix1l2a', 'น้ําหอม'],
            ['xn--q3ca5bk4b5k', 'สํานวน'],
            ['xn--chambres-dhtes-bpb.example', 'chambres-dhôtes.example'],
            ['xn--72cba0e8bxb3cu4kb6d6b.example', 'น้ําใสใจจริง.example'],
            ['xn--bren-mgen-fsse-5hb70axd.example', 'bären-mögen-füsse.example'],
            ['xn--da-hia.example', 'daß.example'],
            ['xn--dmin-moa0i.example', 'dömäin.example'],
            ['xn--aaa-pla.example', 'äaaa.example'],
            ['xn--aaa-qla.example', 'aäaa.example'],
            ['xn--aaa-rla.example', 'aaäa.example'],
            ['xn--aaa-sla.example', 'aaaä.example'],
            ['xn--dj-kia8a.vu.example', 'déjà.vu.example'],
            ['xn--efran-2sa.example', 'efraín.example'],
            ['xn--and-6ma2c.example', 'ñandú.example'],
            ['foo.xn--bcdf-9na9b.example', 'foo.âbcdéf.example'],
            ['xn--4gbrim.xn----ymcbaaajlc6dj7bxne2c.xn--wgbh1c', 'موقع.وزارة-الاتصالات.مصر'],
            ['xn--fuball-cta.example', 'fußball.example'],
            ['xn--18-uldcat6ad6bydd', 'היפא18פאטאם'],
            ['xn--18-dtd1bdi0h3ask', 'فرس18النهر'],
        ];
    }

    public function providerEmailAddress()
    {
        return [
            ['some.user@xn--d1abegsede9b0e.example', 'some.user@мениджмънт.example'],
            ['some.user@xn--uxan.example', 'some.user@πι.example'],
            ['söme.üser@xn--da-hia.example', 'söme.üser@daß.example'],
            ['some.user@foo.xn--bcdf-9na9b.example', 'some.user@foo.âbcdéf.example'],
        ];
    }

    public function providerUrl()
    {
        return [
            [
                'https://user:password@xn--d1abegsede9b0e.example/home/international/test.html',
                'https://user:password@мениджмънт.example/home/international/test.html'
            ],
            [
                'https://üser:päßword@xn--uxan.example/gnörz/lörz/',
                'https://üser:päßword@πι.example/gnörz/lörz/'
            ],
            [
                'https://user:password@xn--da-hia.example/',
                'https://user:password@daß.example/'
            ],
            [
                'https://user:password@foo.xn--bcdf-9na9b.example',
                'https://user:password@foo.âbcdéf.example'
            ],
            [
                'http://xn--and-6ma2c.example',
                'http://ñandú.example'
            ],
            [
                'file:///some/path/xn--somewhere/',
                'file:///some/path/xn--somewhere/'
            ],
        ];
    }
}
